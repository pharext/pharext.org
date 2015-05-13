<?php

namespace app;

require_once __DIR__."/config.php";
require_once __DIR__."/pq.php";

use pq\Connection;

/* @var $injector \Auryn\Injector */

$injector->define(Model\Accounts::class, [
		"conn" => Connection::class,
	])
	->define(Model\Tokens::class, [
		"conn" => Connection::class,
	])
	->define(Model\Authorities::class, [
		"conn" => Connection::class,
	])
	->define(Model\Owners::class, [
		"conn" => Connection::class,
	]);

\pq\Gateway\Table::$defaultResolver = function($table) use($injector) {
	return $injector->make("app\\Model\\" . ucfirst($table));
};

//$modelconf = function($key, $injector) {
//	return new Table($key, $injector->make(Connection::class));
//};
//
//$injector->define(Model\Account::class, [
//	"+accounts" => $modelconf,
//	"+owners" => $modelconf,
//	"+tokens" => $modelconf
//]);
