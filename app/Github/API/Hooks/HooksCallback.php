<?php

namespace app\Github\API\Hooks;

use app\Github\API;
use app\Github\API\Callback;

class HooksCallback extends Callback
{
	private $repo;
	
	function __construct(API $api, $repo) {
		parent::__construct($api);
		$this->repo = $repo;
	}
	
	function exec($json, $links = null) {
		return $this->repo->hooks = $json;
	}
}
