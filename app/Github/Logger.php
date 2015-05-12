<?php

namespace app\Github;

use app\Config;

class Logger extends \Monolog\Logger
{
	function __construct(Config $config) {
		$channel = $config->github->log;
		parent::__construct($channel);
		foreach ($config->log->$channel as $logger) {
			$reflection = new \ReflectionClass("Monolog\\Handler\\" . $logger->handler);
			if (!empty($logger->args)) {
				$handler = $reflection->newInstanceArgs($logger->args->toArray());
			} else {
				$handler = $reflection->newInstance();
			}
			$this->pushHandler($handler);
		}
	}
}
