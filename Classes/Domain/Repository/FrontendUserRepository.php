<?php

declare(strict_types=1);

namespace JwTue\FeUserManager\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository for frontend users.
 *
 * Inherits directly from Extbase\Persistence\Repository. The predecessor version derived
 * from TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository — that class was marked
 * as deprecated in v11 and is **removed** in TYPO3 v12 (the entire directory
 * Extbase/Domain/Repository/ no longer exists). It was an empty subclass of Repository,
 * so no behavior is lost.
 */
class FrontendUserRepository extends Repository
{
    /**
     * Looks up users by their username.
     *
     * Used for the duplicate check when creating new users and therefore has to see the
     * **entire** dataset:
     *
     * - `setRespectStoragePage(false)` — also users outside the configured storage PID,
     *   otherwise a username in a different folder would not be recognized as a
     *   duplicate.
     * - `setIgnoreEnableFields(true)` — also disabled users as well as those outside
     *   their start/end time window.
     * - `setIncludeDeleted(false)` — deleted users remain excluded; their username may
     *   be reassigned.
     *
     * The predecessor version (jw_frontendusermanager) achieved the same via an SQL
     * statement built with the QueryBuilder and `Query::statement()`. That is
     * deliberately replaced here by a regular Extbase query: `statement()` has been
     * deprecated since TYPO3 v11 and is no longer part of QueryInterface.
     *
     * @return QueryResultInterface<\JwTue\FeUserManager\Domain\Model\FrontendUser>
     */
    public function findForUsername(string $username): QueryResultInterface
    {
        $query = $this->createQuery();

        $query->getQuerySettings()
            ->setRespectStoragePage(false)
            ->setIgnoreEnableFields(true)
            ->setIncludeDeleted(false);

        return $query
            ->matching($query->equals('username', $username))
            ->execute();
    }
}
