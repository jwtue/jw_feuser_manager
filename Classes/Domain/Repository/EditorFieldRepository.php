<?php
namespace JwTue\FeUserManager\Domain\Repository;

use JwTue\FeUserManager\Domain\Model\EditorField;

/**
 * @package JwTue\FeUserManager\Domain\Repository
 */
class EditorFieldRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
     /**
      * Returns the editor fields of a content element in sort order.
      *
      * Query::statement() is deliberately still used here, even though the method is
      * deprecated and no longer part of QueryInterface (but still present in TYPO3 v12):
      * the EditorField model maps neither `parent_ce` nor `sorting` as a property, so a
      * regular Extbase query can neither filter nor sort on them. Switching over requires
      * both fields to be added to the model first.
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
