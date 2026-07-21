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
 * Uebertraegt Bestandsdaten der Vorgaenger-Extension jw_frontendusermanager in die
 * jw_feuser_manager-Struktur.
 *
 * Beim Umbenennen der Extension aenderte sich der Datenbank-Praefix von
 * "jwfrontendusermanager" auf "jwfeusermanager" (Konvention: Extension-Key ohne
 * Unterstriche). Die Struktur-Aktualisierung (ext_tables.sql) legt die NEUEN Spalten und
 * die neue Tabelle an, laesst aber die alten mitsamt Daten unangetastet — die
 * Mitgliederfelder saessen sonst nach dem Umzug in verwaisten Alt-Spalten.
 *
 * Vorgehen — bewusst nicht-destruktiv:
 *  - fe_users: fuer jede Alt-Spalte tx_jwfrontendusermanager_* wird der Wert in die
 *    gleichnamige tx_jwfeusermanager_*-Spalte kopiert, aber nur dort, wo die neue Spalte
 *    noch ihren Vorgabewert traegt. So werden weder Nachbearbeitungen ueberschrieben noch
 *    Daten geloescht.
 *  - tx_jwfrontendusermanager_editorfield: die Zeilen werden in die neue Tabelle kopiert,
 *    sofern diese noch leer ist.
 *  - Die Alt-Spalten und die Alt-Tabelle bleiben erhalten. Sie tauchen anschliessend im
 *    Datenbank-Analyzer als "nicht in der Definition" auf und koennen dort nach Sichtpruefung
 *    entfernt werden.
 *
 * Die neuen Namen werden mechanisch aus den alten abgeleitet
 * (jwfrontendusermanager -> jwfeusermanager); es wird nur verarbeitet, was tatsaechlich in
 * der Datenbank existiert. Der Wizard kommt damit ohne hartcodierte Spaltenliste aus.
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
        return 'jw_feuser_manager: Bestandsdaten der Vorgaenger-Extension uebernehmen';
    }

    public function getDescription(): string
    {
        return 'Kopiert Mitgliederfelder (fe_users.tx_jwfrontendusermanager_*) und die '
            . 'Editor-Feld-Datensaetze aus der Vorgaenger-Extension jw_frontendusermanager in '
            . 'die aktuelle jw_feuser_manager-Struktur. Nicht-destruktiv: Alt-Spalten und '
            . 'Alt-Tabelle bleiben erhalten und koennen anschliessend ueber den '
            . 'Datenbank-Analyzer entfernt werden.';
    }

    public function getPrerequisites(): array
    {
        // Die NEUEN Spalten/Tabelle muessen existieren, bevor kopiert werden kann.
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
            'Fertig. Die Alt-Spalten (fe_users.tx_jwfrontendusermanager_*) und die Alt-Tabelle '
            . self::OLD_EDITORFIELD_TABLE . ' wurden bewusst NICHT geloescht. Nach einer '
            . 'Sichtpruefung der uebernommenen Daten koennen sie im Datenbank-Analyzer '
            . '("Nicht in der Definition") entfernt werden.'
        );

        return true;
    }

    // ------------------------------------------------------------------ fe_users

    private function migrateFeUsersColumns(): void
    {
        $columns = $this->feUsersColumnMap();
        if ($columns === []) {
            $this->output->writeln('fe_users: keine Alt-Spalten gefunden.');
            return;
        }

        $connection = $this->connectionPool->getConnectionForTable('fe_users');
        foreach ($columns as $old => [$new, $default]) {
            // Nur dort kopieren, wo die neue Spalte noch ihren Vorgabewert traegt: schuetzt
            // vor dem Ueberschreiben bereits gepflegter Werte und macht den Lauf wiederholbar.
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
            $this->output->writeln(sprintf('  fe_users.%s -> %s: %d Datensatz/-saetze', $old, $new, $affected));
        }
    }

    /**
     * @return array<string, array{0: string, 1: string|null}> Alt-Spalte => [Neu-Spalte, Vorgabewert der Neu-Spalte]
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
     * @return list<string> vorhandene fe_users-Spalten mit Alt-Praefix
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
     * @return array<string, string|null> Spaltenname (lowercase) => Vorgabewert
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

    // ------------------------------------------------------ editorfield-Tabelle

    private function migrateEditorfieldTable(): void
    {
        if (!$this->legacyEditorfieldRowsPending()) {
            $this->output->writeln(self::NEW_EDITORFIELD_TABLE . ': nichts zu uebernehmen.');
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
                . ': keine gemeinsamen Spalten mit der Alt-Tabelle, uebersprungen.</warning>');
            return;
        }

        $quotedOldTable = $oldConnection->quoteIdentifier(self::OLD_EDITORFIELD_TABLE);
        $quotedNewTable = $newConnection->quoteIdentifier(self::NEW_EDITORFIELD_TABLE);
        $quotedColumns = implode(', ', array_map(
            static fn(string $c): string => $newConnection->quoteIdentifier($c),
            $sharedColumns
        ));

        // Gleiche DB-Verbindung fuer beide Tabellen (Standardfall) -> ein INSERT ... SELECT.
        $sql = sprintf('INSERT INTO %s (%s) SELECT %s FROM %s', $quotedNewTable, $quotedColumns, $quotedColumns, $quotedOldTable);
        $affected = (int)$newConnection->executeStatement($sql);
        $this->output->writeln(sprintf(
            '  %s -> %s: %d Datensatz/-saetze (%d Spalten uebernommen)',
            self::OLD_EDITORFIELD_TABLE,
            self::NEW_EDITORFIELD_TABLE,
            $affected,
            count($sharedColumns)
        ));
    }

    /**
     * Wahr, wenn die Alt-Tabelle existiert und Zeilen enthaelt, waehrend die neue Tabelle
     * noch leer ist.
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
