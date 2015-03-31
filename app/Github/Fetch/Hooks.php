<?php

namespace app\Github\Fetch;

use app\Github\Exception\HooksFetchFailed;
use app\Github\Fetch;

use http\Client\Request;

class Hooks extends Fetch
{
	function getRequest() {
		$url = $this->url->mod([
			"path" => "/repos/" . $this->args["repo"] . "/hooks",
		]);
		return new Request("GET", $url, [
			"Accept" => "application/vnd.github.v3+json",
			"Authorization" => "token " . $this->api->getToken(),
		]);
	}

	function getException($message, $code, $previous = null) {
		return new HooksFetchFailed($message, $code, $previous);
	}

	function getCacheKey() {
		return $this->api->getCacheKey(sprintf("hooks:%s", $this->args["repo"]));
	}
}
