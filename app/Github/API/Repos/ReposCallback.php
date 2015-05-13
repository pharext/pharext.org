<?php

namespace app\Github\API\Repos;

use app\Github\API\Callback;
use app\Github\API\Hooks\HooksCallback;

class ReposCallback extends Callback
{
	function __invoke($json, $links = null) {
		foreach ($json as $repo) {
			$this->api->listHooks($repo->full_name, new HooksCallback($this->api, $repo));
		}
	}
}
