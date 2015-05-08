<?php

namespace app;

require_once __DIR__."/config.php";

use pq\Connection;

/* @var $injector \Auryn\Injector */

$pqconfig = function($key, $injector) {
	return $injector->make(Config::class)->pq->$key;
};

$injector->share(Connection::class)
	->define(Connection::class, [
		"+dsn" => $pqconfig,
		"+flags" => $pqconfig
	]);
