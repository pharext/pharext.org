<?php

namespace app;

require_once __DIR__."/config.php";
require_once __DIR__."/pq.php";

use Auryn\Injector;
use pq\Query\Executor;
use pq\Gateway\Table;
use SplSubject, SplObserver;

class QueryLogger extends Logger implements SplObserver
{
	function update(SplSubject $executor) {
		if (($result = $executor->getResult())) {
			$query = $executor->getQuery();
			$this->debug($query, [
				"params" => $query->getParams(),
				"result" => $result
			]);
		}
	}
}

/* @var $injector \Auryn\Injector */

$injector->prepare(Executor::class, function(Executor $executor, Injector $injector) {
	$executor->attach(new QueryLogger($injector->make(Config::class), "query"));
});

foreach ([Model\Accounts::class, Model\Tokens::class, Model\Authorities::class, Model\Owners::class] as $class) {
	$injector->prepare($class, function(Table $table, Injector $injector) {
		$table->setQueryExecutor($injector->make(Executor::class));
	});
}

\pq\Gateway\Table::$defaultResolver = function($table) use($injector) {
	return $injector->make("app\\Model\\" . ucfirst($table));
};
