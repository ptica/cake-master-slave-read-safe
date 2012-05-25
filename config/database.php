<?php
// Database config is responsible for the randomness
class DATABASE_CONFIG {

	public static $default;

	public function __construct() {
		$master = array(
			'driver' => 'master-slave.DboMysqlMasterSlave', // Or DboMysqliMasterSlave
			'persistent' => false,
			'host' => 'master',
			'port' => 3306,
			'login' => 'root',
			'password' => 'password',
			'database' => 'database',
			'encoding' => 'utf8',
		);

		$slaves = array(
			array(
				'driver' => 'master-slave.DboMysqlMasterSlave', // Or DboMysqliMasterSlave
				'persistent' => false,
				'host' => 'slave1',
				'port' => 3306,
				'login' => 'root',
				'password' => 'password',
				'database' => 'database',
				'encoding' => 'utf8',
				),
			array(
				'driver' => 'master-slave.DboMysqlMasterSlave', // Or DboMysqliMasterSlave
				'persistent' => false,
				'host' => 'slave2',
				'port' => 3306,
				'login' => 'root',
				'password' => 'password',
				'database' => 'database',
				'encoding' => 'utf8',
			)
		);

		$this->master = $master;
		if(!isset($this->default)) {
			$this->default = $slaves[rand(0, count($slaves) - 1)];
		}
	}
}
