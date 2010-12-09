<?php
App::import('Datasource', 'DboMysqli');
class DboMysqlislave extends DboMysqli {
	public function _execute($sql) {
		$updates = array('CREATE', 'DELETE', 'DROP', 'INSERT', 'UPDATE', 'TRUNCATE', 'REPLACE');
        $datasource = preg_match('/^(' . implode('|', $updates) . ')/i', trim($sql)) ? 'master' : 'default';

		$this->setConnection($datasource);

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
?>
