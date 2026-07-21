<?php

declare(strict_types=1);

namespace JwTue\FeUserManager\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository for frontend user groups.
 *
 * Only exists so that the persistence mapping in
 * Configuration/Extbase/Persistence/Classes.php resolves to the own model
 * JwTue\FeUserManager\Domain\Model\FrontendUserGroup.
 *
 * Inherits directly from Extbase\Persistence\Repository — the class previously used as
 * a base, TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository, is removed in
 * TYPO3 v12 and was likewise only an empty subclass of Repository.
 */
class FrontendUserGroupRepository extends Repository
{
}
