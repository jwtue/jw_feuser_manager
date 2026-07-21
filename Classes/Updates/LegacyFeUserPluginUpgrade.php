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
 * Migrates tt_content plugins of the predecessor frontend user management to the
 * current jw_feuser_manager signatures.
 *
 * Generic (not client-specific): the signature change
 * jwfrontendusermanager_* -> jwfeusermanager_* affects every installation that
 * comes from the old extension.
 *
 * Idempotent: updateNecessary() checks whether legacy signatures are still present;
 * running it multiple times is harmless.
 *
 * datamints_feuser_pi1 (an older, third-party extension) is deliberately NOT
 * migrated automatically but only reported – the appropriate target plugin is
 * not unambiguous and must be decided manually.
 */
#[UpgradeWizard('jwFeUserManager_legacyPluginSignature')]
final class LegacyFeUserPluginUpgrade implements UpgradeWizardInterface, ChattyInterface, RepeatableInterface
{
    private const TABLE = 'tt_content';

    /**
     * Old list_type signature => current jw_feuser_manager signature.
     * Targets from Configuration/TCA/Overrides/tt_content.php (registerPlugin
     * 'JwFeUserManager'/'ListOfUsers'|'EditUser'); source values verified on 2026-07-21
     * against the staging DB feuerwzq_t3_12.
     */
    private const LIST_TYPE_MAP = [
        'jwfrontendusermanager_listofusers' => 'jwfeusermanager_listofusers',
        'jwfrontendusermanager_edituser'    => 'jwfeusermanager_edituser',
        // Dotted variant from an earlier registration attempt (JwTue.FeUserManager):
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
        return 'jw_feuser_manager: migrate old plugin signatures';
    }

    public function getDescription(): string
    {
        return 'Rewrites tt_content.list_type of the predecessor signatures '
            . '(jwfrontendusermanager_listofusers/_edituser as well as the dotted variant '
            . 'jwtue.feusermanager_listofusers) to the current jw_feuser_manager values. '
            . 'datamints_feuser_pi1 is only reported, not migrated.';
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
                $this->output->writeln(sprintf('  %s -> %s: %d element(s)', $old, $new, $affected));
            }
            $total += (int)$affected;
        }
        $this->output->writeln(sprintf('Migrated: %d element(s).', $total));

        $datamints = (int)$connection->count('uid', self::TABLE, ['list_type' => 'datamints_feuser_pi1']);
        if ($datamints > 0) {
            $this->output->writeln(sprintf(
                '<warning>%d element(s) with list_type "datamints_feuser_pi1" found – NOT migrated. '
                . 'Please check manually whether these should be replaced by jw_feuser_manager.</warning>',
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
