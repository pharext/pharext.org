<?php

namespace app\Github\API;

use app\Github\API;

abstract class Callback
{
	protected $api;
	
	abstract protected function exec($json, $links = null);
	
	function __construct(API $api) {
		$this->api = $api;
	}
	
	function __invoke($result) {
		return $this->exec(...$result);
	}
}
