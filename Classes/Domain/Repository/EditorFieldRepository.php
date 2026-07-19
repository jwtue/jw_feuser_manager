<?php
namespace JwTue\FeUserManager\Domain\Repository;

use JwTue\FeUserManager\Domain\Model\EditorField;

/**
 * @package JwTue\FeUserManager\Domain\Repository
 */
class EditorFieldRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
     /**
      * Liefert die Editor-Felder eines Inhaltselements in Sortierreihenfolge.
      *
      * Hier wird bewusst weiterhin Query::statement() verwendet, obwohl die Methode
      * deprecated und nicht mehr Teil von QueryInterface ist (in TYPO3 v12 aber
      * weiterhin vorhanden): Das Model EditorField mappt weder `parent_ce` noch
      * `sorting` als Eigenschaft, eine reguläre Extbase-Query kann darauf also weder
      * filtern noch sortieren. Eine Umstellung setzt voraus, dass beide Felder zuvor
      * im Model ergänzt werden.
      */
     public function findForElement($pid, $cid)
     {
		$connPool = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class);
		$conn = $connPool->getConnectionForTable('tx_jwfeusermanager_editorfield');
		$queryBuilder = $conn->createQueryBuilder();
		
		$q = $queryBuilder
				->select("*")
				->from("tx_jwfeusermanager_editorfield")
				->where(
					$queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pid))
				)->andWhere(
					$queryBuilder->expr()->eq('parent_ce', $queryBuilder->createNamedParameter($cid))
				)->orderBy('sorting');
		
		$query = $this->createQuery();
		
		$query->statement($q->getSQL(), $q->getParameters());
		
		return $query->execute();
     }
}
