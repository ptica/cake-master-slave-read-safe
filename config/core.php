<?php 
	Cache::config('master-slave-safe-read-period', array(
		'engine' => 'File',
		'path' => CACHE,
		'duration'=> '+5 seconds',
		'prefix' => 'safe_read_'
	));
