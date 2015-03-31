<?php

namespace app\Github\Fetch;

use app\Github\Exception\ReposFetchFailed;
use app\Github\Fetch;

use http\Client\Request;

class Repo extends Fetch
{
	function getRequest() {
		$url = $this->url->mod("/repos/" . $this->args["repo"]);
		return new Request("GET", $url, [
			"Accept" => "application/vnd.github.v3+json",
			"Authorization" => "token " . $this->api->getToken(),
		]);
	}

	function getException($message, $code, $previous = null) {
		return new ReposFetchFailed($message, $code, $previous);
	}

	function getCacheKey() {
		return $this->api->getCacheKey(sprintf("repo:%s", $this->args["repo"]));
	}
}
