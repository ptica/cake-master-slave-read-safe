<?php
App::import('Datasource', 'DboMysql');
class DboMysqlMasterSlave extends DboMysql {

	public $description = "MySQL DBO Driver with support for Master/Slave database setup";

	/**
	 * Override execute to use master or slave connection
	 *
	 * @param string $sql 
	 * @return resource
	 */
	public function _execute($sql) {
		$updates = array('CREATE', 'DELETE', 'DROP', 'INSERT', 'UPDATE', 'TRUNCATE', 'REPLACE', 'START TRANSACTION', 'COMMIT', 'ROLLBACK');
		
		$trimmed_sql = trim($sql);
		
		
		if (preg_match('/^(SET NAMES)/i', $trimmed_sql)) {
			// not needed to set a connection
			// as 'set names' is invoked in the connection constructor
			// whether 'encoding' is specified on the connection
			// beware though: explicitly setting a connection here
			// results in connection constructor being called again
			// (and again and again ...)
		} else {
			$datasource = preg_match('/^(' . implode('|', $updates) . ')/i', $trimmed_sql) ? 'master' : 'default';
			$this->setConnection($datasource);
			debug($sql);
			debug($datasource);
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