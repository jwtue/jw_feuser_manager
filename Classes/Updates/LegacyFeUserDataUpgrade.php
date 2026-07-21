<?php

declare(strict_types=1);

namespace JwTue\FeUserManager\Updates;

use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Transfers legacy data of the predecessor extension jw_frontendusermanager into the
 * jw_feuser_manager structure.
 *
 * When the extension was renamed, the database prefix changed from
 * "jwfrontendusermanager" to "jwfeusermanager" (convention: extension key without
 * underscores). The structure update (ext_tables.sql) creates the NEW columns and the
 * new table, but leaves the old ones and their data untouched — otherwise the member
 * fields would end up in orphaned legacy columns after the move.
 *
 * Approach — deliberately non-destructive:
 *  - fe_users: for each legacy column tx_jwfrontendusermanager_* the value is copied into
 *    the identically named tx_jwfeusermanager_* column, but only where the new column
 *    still carries its default value. This way neither later edits are overwritten nor
 *    data is deleted.
 *  - tx_jwfrontendusermanager_editorfield: the rows are copied into the new table,
 *    provided it is still empty.
 *  - The legacy columns and the legacy table are kept. They subsequently show up in the
 *    database analyzer as "not in the definition" and can be removed there after a visual
 *    review.
 *
 * The new names are derived mechanically from the old ones
 * (jwfrontendusermanager -> jwfeusermanager); only what actually exists in the database
 * is processed. The wizard thus works without a hardcoded column list.
 */
#[UpgradeWizard('jwFeUserManager_legacyData')]
final class LegacyFeUserDataUpgrade implements UpgradeWizardInterface, ChattyInterface, RepeatableInterface
{
    private const OLD_PREFIX = 'jwfrontendusermanager';
    private const NEW_PREFIX = 'jwfeusermanager';

    private const OLD_EDITORFIELD_TABLE = 'tx_jwfrontendusermanager_editorfield';
    private const NEW_EDITORFIELD_TABLE = 'tx_jwfeusermanager_editorfield';

    private OutputInterface $output;

    public function __construct(private readonly ConnectionPool $connectionPool)
    {
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function getTitle(): string
    {
        return 'jw_feuser_manager: import legacy data from the predecessor extension';
    }

    public function getDescription(): string
    {
        return 'Copies member fields (fe_users.tx_jwfrontendusermanager_*) and the '
            . 'editor field records from the predecessor extension jw_frontendusermanager into '
            . 'the current jw_feuser_manager structure. Non-destructive: legacy columns and '
            . 'legacy table are kept and can subsequently be removed via the '
            . 'database analyzer.';
    }

    public function getPrerequisites(): array
    {
        // The NEW columns/table must exist before anything can be copied.
        return [DatabaseUpdatedPrerequisite::class];
    }

    public function updateNecessary(): bool
    {
        return $this->legacyFeUsersColumns() !== [] || $this->legacyEditorfieldRowsPending();
    }

    public function executeUpdate(): bool
    {
        $this->migrateFeUsersColumns();
        $this->migrateEditorfieldTable();

        $this->output->writeln('');
        $this->output->writeln(
            'Done. The legacy columns (fe_users.tx_jwfrontendusermanager_*) and the legacy table '
            . self::OLD_EDITORFIELD_TABLE . ' were deliberately NOT deleted. After a '
            . 'visual review of the imported data they can be removed in the database analyzer '
            . '("Not in the definition").'
        );

        return true;
    }

    // ------------------------------------------------------------------ fe_users

    private function migrateFeUsersColumns(): void
    {
        $columns = $this->feUsersColumnMap();
        if ($columns === []) {
            $this->output->writeln('fe_users: no legacy columns found.');
            return;
        }

        $connection = $this->connectionPool->getConnectionForTable('fe_users');
        foreach ($columns as $old => [$new, $default]) {
            // Only copy where the new column still carries its default value: this protects
            // against overwriting already maintained values and makes the run repeatable.
            $sql = sprintf(
                'UPDATE %s SET %s = %s WHERE %s <=> %s AND NOT (%s <=> %s)',
                $connection->quoteIdentifier('fe_users'),
                $connection->quoteIdentifier($new),
                $connection->quoteIdentifier($old),
                $connection->quoteIdentifier($new),
                $connection->quote((string)$default),
                $connection->quoteIdentifier($old),
                $connection->quote((string)$default)
            );
            $affected = (int)$connection->executeStatement($sql);
            $this->output->writeln(sprintf('  fe_users.%s -> %s: %d record(s)', $old, $new, $affected));
        }
    }

    /**
     * @return array<string, array{0: string, 1: string|null}> legacy column => [new column, default value of the new column]
     */
    private function feUsersColumnMap(): array
    {
        $existing = $this->columnDefaults('fe_users');
        $map = [];
        foreach ($this->legacyFeUsersColumns() as $old) {
            $new = str_replace(self::OLD_PREFIX, self::NEW_PREFIX, $old);
            if (array_key_exists($new, $existing)) {
                $map[$old] = [$new, $existing[$new]];
            }
        }

        return $map;
    }

    /**
     * @return list<string> existing fe_users columns with the legacy prefix
     */
    private function legacyFeUsersColumns(): array
    {
        $legacy = [];
        foreach (array_keys($this->columnDefaults('fe_users')) as $name) {
            if (str_contains($name, self::OLD_PREFIX)) {
                $legacy[] = $name;
            }
        }

        return $legacy;
    }

    /**
     * @return array<string, string|null> column name (lowercase) => default value
     */
    private function columnDefaults(string $table): array
    {
        $connection = $this->connectionPool->getConnectionForTable($table);
        $defaults = [];
        foreach ($connection->createSchemaManager()->listTableColumns($table) as $column) {
            $defaults[strtolower($column->getName())] = $column->getDefault();
        }

        return $defaults;
    }

    // ------------------------------------------------------ editorfield table

    private function migrateEditorfieldTable(): void
    {
        if (!$this->legacyEditorfieldRowsPending()) {
            $this->output->writeln(self::NEW_EDITORFIELD_TABLE . ': nothing to import.');
            return;
        }

        $oldConnection = $this->connectionPool->getConnectionForTable(self::OLD_EDITORFIELD_TABLE);
        $newConnection = $this->connectionPool->getConnectionForTable(self::NEW_EDITORFIELD_TABLE);

        $sharedColumns = array_values(array_intersect(
            array_keys($this->columnDefaults(self::OLD_EDITORFIELD_TABLE)),
            array_keys($this->columnDefaults(self::NEW_EDITORFIELD_TABLE))
        ));
        if ($sharedColumns === []) {
            $this->output->writeln('<warning>' . self::NEW_EDITORFIELD_TABLE
                . ': no columns in common with the legacy table, skipped.</warning>');
            return;
        }

        $quotedOldTable = $oldConnection->quoteIdentifier(self::OLD_EDITORFIELD_TABLE);
        $quotedNewTable = $newConnection->quoteIdentifier(self::NEW_EDITORFIELD_TABLE);
        $quotedColumns = implode(', ', array_map(
            static fn(string $c): string => $newConnection->quoteIdentifier($c),
            $sharedColumns
        ));

        // Same DB connection for both tables (the standard case) -> a single INSERT ... SELECT.
        $sql = sprintf('INSERT INTO %s (%s) SELECT %s FROM %s', $quotedNewTable, $quotedColumns, $quotedColumns, $quotedOldTable);
        $affected = (int)$newConnection->executeStatement($sql);
        $this->output->writeln(sprintf(
            '  %s -> %s: %d record(s) (%d columns imported)',
            self::OLD_EDITORFIELD_TABLE,
            self::NEW_EDITORFIELD_TABLE,
            $affected,
            count($sharedColumns)
        ));
    }

    /**
     * True if the legacy table exists and contains rows while the new table is still
     * empty.
     */
    private function legacyEditorfieldRowsPending(): bool
    {
        if (!$this->tableExists(self::OLD_EDITORFIELD_TABLE) || !$this->tableExists(self::NEW_EDITORFIELD_TABLE)) {
            return false;
        }

        return $this->rowCount(self::OLD_EDITORFIELD_TABLE) > 0 && $this->rowCount(self::NEW_EDITORFIELD_TABLE) === 0;
    }

    private function tableExists(string $table): bool
    {
        $connection = $this->connectionPool->getConnectionForTable($table);

        return in_array($table, $connection->createSchemaManager()->listTableNames(), true);
    }

    private function rowCount(string $table): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();

        return (int)$queryBuilder->count('uid')->from($table)->executeQuery()->fetchOne();
    }
}
