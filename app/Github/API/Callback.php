<?php

namespace app\Github\API;

use app\Github\API;

abstract class Callback
{
	protected $api;
	
	abstract function __invoke($json, $links = null);
	
	function __construct(API $api) {
		$this->api = $api;
	}
}
