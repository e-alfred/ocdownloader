<?php 

namespace OCA\ocDownloader\Service;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\Security\ICrypto;

use OCP\IUser;

/**
 * Stores the mount config in the database
 */
class DBService {
	
	/**
	 * @var IDBConnection
	 */
	private $connection;

  /**
	 * DBService constructor.
	 *
	 * @param ICrypto $crypto
	 */
	public function __construct(IDBConnection $connection) {
		$this->connection = $connection;
  }
  
  /**
	 * @param IUser $user
	 * @return array
	 */
	public function getQueueByUser(IUser $user, Array $status = []) {
    $builder = $this->connection->getQueryBuilder();
    
		$query = $builder->select('*')
			->from('ocdownloader_queue')
			->where(
        $builder->expr()->eq('UID', $builder->createNamedParameter($user->getUID(), IQueryBuilder::PARAM_STR))
      )
			->andwhere(
				$builder->expr()->in('STATUS', $builder->createNamedParameter($status, IQueryBuilder::PARAM_INT_ARRAY))
      )
			->andwhere(
				$builder->expr()->in('IS_CLEANED', $builder->createNamedParameter([0,1], IQueryBuilder::PARAM_INT_ARRAY))
      );
		
    $result = $query->execute();
    
    return $result;
    
	}
	
	public function addQueue($CurrentUID, $AddURI) {
		
		$qb = $this->connection->getQueryBuilder();
 		$qb->insert('ocdownloader_queue')
			->values([
			 'UID' => $qb->createNamedParameter($CurrentUID),
			 'GID' => $qb->createNamedParameter($AddURI['result']),
			 'FILENAME' => $qb->createNamedParameter($AddURI['out']),
			 'PROTOCOL' => $qb->createNamedParameter($AddURI['PROTOCOL']),
			 'STATUS' => $qb->createNamedParameter(1),
			 'TIMESTAMP' => time(),
			 ]);
			$ret = $qb->execute();
	}
		
}
