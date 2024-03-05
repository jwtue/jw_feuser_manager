<?php
namespace JwTue\FeUserManager\Domain\Repository;

/**
 * A repository for feusers
 */
class FrontendUserRepository extends \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository
{
/*	public function findForUid($user) {
		$connPool = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class);
		$conn = $connPool->getConnectionForTable('fe_users');
		$queryBuilder = $conn->createQueryBuilder();
				
		$query = $this->createQuery();
		$q = $queryBuilder
				->select("*")
				->from("fe_users")
				->where(
					$queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($user))
				);
		\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(array($q->getSQL(), $q->getParameters()));
		$query->statement($q->getSQL(), $q->getParameters());
		
		return $query->execute();
	}*/
	
	public function findForUsername($username) {
		$connPool = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class);
		$conn = $connPool->getConnectionForTable('fe_users');
		$queryBuilder = $conn->createQueryBuilder();
		$queryBuilder->getRestrictions()->removeByType(\TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction::class);
		$queryBuilder->getRestrictions()->removeByType(\TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction::class);
		$queryBuilder->getRestrictions()->removeByType(\TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction::class);
				
		$query = $this->createQuery();
		$q = $queryBuilder
				->select("*")
				->from("fe_users")
				->where(
					$queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter($username))
				);
		//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(array($q->getSQL(), $q->getParameters()));
		$query->statement($q->getSQL(), $q->getParameters());
		
		return $query->execute();
	}
}
