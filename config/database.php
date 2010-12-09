<?php
// Database config is responsible for the randomness
class DATABASE_CONFIG {

	public static $default;

	public function __construct() {
		$master = array(
			'driver' => 'mysqlislave',
			'persistent' => false,
			'host' => 'master',
			'port' => 3306,
			'login' => 'root',
			'password' => 'password',
			'database' => 'database',
		);

		$slaves = array(
			array(
				'driver' => 'mysqlislave',
				'persistent' => false,
				'host' => 'slave1',
				'port' => 3306,
				'login' => 'root',
				'password' => 'password',
				'database' => 'database',
			),
			array(
				'driver' => 'mysqlislave',
				'persistent' => false,
				'host' => 'slave2',
				'port' => 3306,
				'login' => 'root',
				'password' => 'password',
				'database' => 'database',
			)
		);

		$this->master = $master;
		if(!isset($this->default)) {
			$this->default = $slaves[rand(0, count($slaves) - 1)];
		}
	}
}
