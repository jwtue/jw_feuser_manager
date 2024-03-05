<?php
namespace JwTue\FeUserManager\Domain\Repository;

use JwTue\FeUserManager\Domain\Model\EditorField;

/**
 * @package JwTue\FeUserManager\Domain\Repository
 */
class EditorFieldRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
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
