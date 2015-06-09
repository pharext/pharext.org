<?php

namespace app\Github\API\Repos;

use app\Github\API\Callback;
use app\Github\API\Hooks\HooksCallback;

use React\Promise;

class ReposCallback extends Callback
{
	protected function exec($json, $links = null) {
		$promises = array([$json, $links]);
		foreach ($json as $repo) {
			$promises[] = $this->api
				->listHooks($repo->full_name)
				->then(new HooksCallback($this->api, $repo));
		}
		return Promise\all($promises);
	}
}
