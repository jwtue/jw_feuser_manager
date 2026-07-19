<?php

declare(strict_types=1);

namespace JwTue\FeUserManager\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository für Frontend-Benutzergruppen.
 *
 * Existiert nur, damit das Persistence-Mapping in
 * Configuration/Extbase/Persistence/Classes.php auf das eigene Model
 * JwTue\FeUserManager\Domain\Model\FrontendUserGroup greift.
 *
 * Erbt direkt von Extbase\Persistence\Repository — die zuvor als Basis genutzte Klasse
 * TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository ist in TYPO3 v12
 * entfernt und war ebenfalls nur eine leere Ableitung von Repository.
 */
class FrontendUserGroupRepository extends Repository
{
}
