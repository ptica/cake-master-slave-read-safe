<?php
App::import('Datasource', 'DboMysql');
class DboMysqlMasterSlaveReadSafe extends DboMysql {

	public $description = "MySQL DBO Driver with support for Master/Slave database setup";
	
	private $updates = array('CREATE', 'DELETE', 'DROP', 'INSERT', 'UPDATE', 'TRUNCATE', 'REPLACE', 'START TRANSACTION', 'COMMIT', 'ROLLBACK');
	private $updates_regexp;

	public function __construct($config = null, $autoConnect = true) {
		$this->updates_regexp = '/^(' . implode('|', $this->updates) . ')/i';
		
		return parent::__construct($config, $autoConnect);
	}

	/**
	 * Override execute to use master or slave connection
	 *
	 * @param string $sql 
	 * @return resource
	 */
	public function _execute($sql) {
		$trimmed_sql = trim($sql);
		
		
		if (preg_match('/^(SET NAMES)/i', $trimmed_sql)) {
			// not needed to set a connection
			// as 'set names' is invoked in the connection constructor
			// whether 'encoding' is specified on the connection
			// beware though: explicitly setting a connection here
			// results in connection constructor being called again
			// (and again and again ...)
		} else {
			$datasource = preg_match($this->updates_regexp, $trimmed_sql) ? 'master' : 'default';
			
			if ($datasource == 'master') {
				// safe reads logic: 
				// upon writes we resort to reads from master for a defined period 
				Cache::write('MasterSlaveSafeRead', 1, 'master-slave-safe-read-period');
			} else if (Cache::read('MasterSlaveSafeRead', 'master-slave-safe-read-period')) {
				// slave selected:
				// doublecheck the safe read period:
				// switch to master if a write has been executed previously
				$datasource = 'master'; 
			}
			
			$this->setConnection($datasource);
		}

		return parent::_execute($sql);
	}

	/**
	 * Switch the datasource to 'master' when beginning a transaction
	 */
	public function begin(&$model) {
		$this->setConnection('master');

		return parent::begin($model);
	}

	/**
	 * Switch the connection based on name
	 * Accepted names are 'master' and 'default' (a slave)
	 * If in the middle of a transaction the 'master' connection
	 * will always be used.
	 */
	protected function setConnection($name='default') {
		if($this->_transactionStarted) {
			$name = 'master';
		}
		
		$datasource = ConnectionManager::getDataSource($name);

		if(!$datasource->isConnected())	{
			$datasource->connect();
		}

		$this->connection = $datasource->connection;
	}
}