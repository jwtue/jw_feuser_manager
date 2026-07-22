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
 * Migrates the plugin content elements to dedicated CTypes for TYPO3 v14.
 *
 * TYPO3 v14 removed the list_type mechanism: plugins are now their own CType. On
 * installations upgraded from v12/v13 the content elements still read
 * CType='list' with list_type='jwfeusermanager_listofusers'|'_edituser' (or a
 * predecessor signature). This wizard rewrites them to CType='jwfeusermanager_*'.
 *
 * Why this works even though wizards run *after* the upgrade: TYPO3's database
 * schema update never drops columns on its own — removing an unused column is a
 * separate, explicitly confirmed step in the Install Tool. The tt_content.list_type
 * column therefore still physically exists after the upgrade (only missing from the
 * TCA), so this wizard can read it via direct SQL. A guard skips everything when the
 * column is absent (a fresh v14 install), which makes the wizard a safe no-op there.
 * After running it, the now-unused list_type column can be removed in the analyzer.
 *
 * Idempotent/repeatable: updateNecessary() checks for remaining legacy rows.
 * datamints_feuser_pi1 is only reported, not migrated — the correct target is not
 * unambiguous and must be decided manually.
 */
#[UpgradeWizard('jwFeUserManager_listTypeToCType')]
final class LegacyFeUserPluginUpgrade implements UpgradeWizardInterface, ChattyInterface, RepeatableInterface
{
    private const TABLE = 'tt_content';

    /**
     * Legacy list_type value => target CType. The current jw_feuser_manager CTypes
     * equal the former jw_feuser_manager list_type values; the predecessor and dotted
     * signatures are folded onto the same targets so a v11/v12/v13 -> v14 jump migrates
     * in one step.
     */
    private const LIST_TYPE_TO_CTYPE = [
        'jwfeusermanager_listofusers'       => 'jwfeusermanager_listofusers',
        'jwfeusermanager_edituser'          => 'jwfeusermanager_edituser',
        'jwfrontendusermanager_listofusers' => 'jwfeusermanager_listofusers',
        'jwfrontendusermanager_edituser'    => 'jwfeusermanager_edituser',
        'jwtue.feusermanager_listofusers'   => 'jwfeusermanager_listofusers',
    ];

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
        return 'jw_feuser_manager: migrate list_type plugins to CType (v14)';
    }

    public function getDescription(): string
    {
        return 'Rewrites content elements from the removed list_type mechanism '
            . '(CType="list", list_type="jwfeusermanager_*" and the predecessor signatures) '
            . 'to the dedicated CTypes jwfeusermanager_listofusers / _edituser. Reads the '
            . 'still-present list_type column directly; a no-op on fresh v14 installs where '
            . 'that column does not exist. datamints_feuser_pi1 is only reported.';
    }

    public function getPrerequisites(): array
    {
        return [DatabaseUpdatedPrerequisite::class];
    }

    public function updateNecessary(): bool
    {
        return $this->listTypeColumnExists() && $this->countLegacy() > 0;
    }

    public function executeUpdate(): bool
    {
        if (!$this->listTypeColumnExists()) {
            $this->output->writeln('tt_content.list_type column not present — nothing to migrate.');
            return true;
        }

        $total = 0;
        foreach (self::LIST_TYPE_TO_CTYPE as $old => $cType) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
            $affected = (int)$queryBuilder
                ->update(self::TABLE)
                ->set('CType', $cType)
                // Clear list_type so the row is no longer counted as legacy (the column is
                // still there until it is dropped manually in the database analyzer).
                ->set('list_type', '')
                ->where(
                    $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('list')),
                    $queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter($old))
                )
                ->executeStatement();
            if ($affected > 0) {
                $this->output->writeln(sprintf('  list_type "%s" -> CType "%s": %d element(s)', $old, $cType, $affected));
            }
            $total += $affected;
        }
        $this->output->writeln(sprintf('Migrated: %d element(s).', $total));

        $datamints = $this->countByListType('datamints_feuser_pi1');
        if ($datamints > 0) {
            $this->output->writeln(sprintf(
                '<warning>%d element(s) with list_type "datamints_feuser_pi1" found – NOT migrated. '
                . 'Please check manually whether these should be replaced by jw_feuser_manager.</warning>',
                $datamints
            ));
        }

        return true;
    }

    private function listTypeColumnExists(): bool
    {
        $connection = $this->connectionPool->getConnectionForTable(self::TABLE);
        $columns = array_map(
            'strtolower',
            array_keys($connection->createSchemaManager()->listTableColumns(self::TABLE))
        );

        return in_array('list_type', $columns, true);
    }

    private function countLegacy(): int
    {
        $total = 0;
        foreach (array_keys(self::LIST_TYPE_TO_CTYPE) as $old) {
            $total += $this->countByListType($old);
        }

        return $total;
    }

    private function countByListType(string $listType): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $queryBuilder->getRestrictions()->removeAll();

        return (int)$queryBuilder
            ->count('uid')
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('list')),
                $queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter($listType))
            )
            ->executeQuery()
            ->fetchOne();
    }
}
