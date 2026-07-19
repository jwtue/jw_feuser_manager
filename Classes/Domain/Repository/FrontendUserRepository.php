<?php

declare(strict_types=1);

namespace JwTue\FeUserManager\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository für Frontend-Benutzer.
 *
 * Erbt direkt von Extbase\Persistence\Repository. Die Vorgängerfassung leitete von
 * TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository ab — diese Klasse war in
 * v11 als deprecated markiert und ist in TYPO3 v12 **entfernt** (das gesamte
 * Verzeichnis Extbase/Domain/Repository/ existiert nicht mehr). Sie war eine leere
 * Ableitung von Repository, es geht dabei also kein Verhalten verloren.
 */
class FrontendUserRepository extends Repository
{
    /**
     * Sucht Benutzer anhand des Benutzernamens.
     *
     * Wird für die Dublettenprüfung beim Anlegen neuer Benutzer verwendet und muss
     * deshalb den **gesamten** Bestand sehen:
     *
     * - `setRespectStoragePage(false)` — auch Benutzer außerhalb der konfigurierten
     *   Storage-PID, sonst würde ein Benutzername in einem anderen Ordner nicht als
     *   Dublette erkannt.
     * - `setIgnoreEnableFields(true)` — auch deaktivierte Benutzer sowie solche
     *   außerhalb ihres Start-/Endzeitfensters.
     * - `setIncludeDeleted(false)` — gelöschte Benutzer bleiben ausgenommen; ihr
     *   Benutzername darf neu vergeben werden.
     *
     * Die Vorgängerfassung (jw_frontendusermanager) hat dasselbe über ein per
     * QueryBuilder gebautes SQL-Statement und `Query::statement()` erreicht. Das ist
     * hier bewusst durch eine reguläre Extbase-Query ersetzt: `statement()` ist seit
     * TYPO3 v11 deprecated und nicht mehr Teil von QueryInterface.
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
