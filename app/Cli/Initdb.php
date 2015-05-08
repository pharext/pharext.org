<?php

namespace app\Cli;

use app\Controller;
use pq\Connection;

class Initdb implements Controller
{
	private $pq;
	
	function __construct(Connection $pq) {
		$this->pq = $pq;
	}
	
	function __invoke(array $args = null) {
		foreach (glob(__DIR__."/../../config/sql/*.sql") as $sql) {
			$xa = $this->pq->startTransaction();
			$this->pq->exec(file_get_contents($sql));
			$xa->commit();
		}
	}
}
