<?php

declare(strict_types=1);

namespace JwTue\FeUserManager\Updates;

use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Migriert tt_content-Plugins der Vorgaenger-Frontenduser-Verwaltung auf die
 * aktuellen jw_feuser_manager-Signaturen.
 *
 * Generisch (nicht mandantenspezifisch): der Signaturwechsel
 * jwfrontendusermanager_* -> jwfeusermanager_* betrifft jede Installation, die
 * von der alten Extension kommt.
 *
 * Idempotent: updateNecessary() prueft, ob noch Alt-Signaturen vorhanden sind;
 * mehrfaches Ausfuehren ist gefahrlos.
 *
 * datamints_feuser_pi1 (aeltere, fremde Extension) wird bewusst NICHT
 * automatisch migriert, sondern nur gemeldet – der passende Ziel-Plugin ist
 * nicht eindeutig und muss manuell entschieden werden.
 */
#[UpgradeWizard('jwFeUserManager_legacyPluginSignature')]
final class LegacyFeUserPluginUpgrade implements UpgradeWizardInterface, ChattyInterface, RepeatableInterface
{
    private const TABLE = 'tt_content';

    /**
     * Alte list_type-Signatur => aktuelle jw_feuser_manager-Signatur.
     * Ziele aus Configuration/TCA/Overrides/tt_content.php (registerPlugin
     * 'JwFeUserManager'/'ListOfUsers'|'EditUser'); Quellwerte am 2026-07-21
     * gegen die Staging-DB feuerwzq_t3_12 verifiziert.
     */
    private const LIST_TYPE_MAP = [
        'jwfrontendusermanager_listofusers' => 'jwfeusermanager_listofusers',
        'jwfrontendusermanager_edituser'    => 'jwfeusermanager_edituser',
        // Dotted-Variante aus einem frueheren Registrierungsversuch (JwTue.FeUserManager):
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
        return 'jw_feuser_manager: Alte Plugin-Signaturen migrieren';
    }

    public function getDescription(): string
    {
        return 'Schreibt tt_content.list_type der Vorgaenger-Signaturen '
            . '(jwfrontendusermanager_listofusers/_edituser sowie die Dotted-Variante '
            . 'jwtue.feusermanager_listofusers) auf die aktuellen jw_feuser_manager-Werte um. '
            . 'datamints_feuser_pi1 wird nur gemeldet, nicht migriert.';
    }

    public function getPrerequisites(): array
    {
        return [DatabaseUpdatedPrerequisite::class];
    }

    public function updateNecessary(): bool
    {
        return $this->countLegacy() > 0;
    }

    public function executeUpdate(): bool
    {
        $connection = $this->connectionPool->getConnectionForTable(self::TABLE);
        $total = 0;
        foreach (self::LIST_TYPE_MAP as $old => $new) {
            $affected = $connection->update(self::TABLE, ['list_type' => $new], ['list_type' => $old]);
            if ($affected > 0) {
                $this->output->writeln(sprintf('  %s -> %s: %d Element(e)', $old, $new, $affected));
            }
            $total += (int)$affected;
        }
        $this->output->writeln(sprintf('Migriert: %d Element(e).', $total));

        $datamints = (int)$connection->count('uid', self::TABLE, ['list_type' => 'datamints_feuser_pi1']);
        if ($datamints > 0) {
            $this->output->writeln(sprintf(
                '<warning>%d Element(e) mit list_type "datamints_feuser_pi1" gefunden – NICHT migriert. '
                . 'Bitte manuell pruefen, ob diese durch jw_feuser_manager ersetzt werden.</warning>',
                $datamints
            ));
        }

        return true;
    }

    private function countLegacy(): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $queryBuilder->getRestrictions()->removeAll();

        return (int)$queryBuilder
            ->count('uid')
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->in(
                    'list_type',
                    $queryBuilder->createNamedParameter(
                        array_keys(self::LIST_TYPE_MAP),
                        Connection::PARAM_STR_ARRAY
                    )
                )
            )
            ->executeQuery()
            ->fetchOne();
    }
}
