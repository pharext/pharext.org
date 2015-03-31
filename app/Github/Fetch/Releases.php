<?php

namespace app\Github\Fetch;

use app\Github\Exception\ReleasesFetchFailed;
use app\Github\Fetch;

use http\Client\Request;
use http\QueryString;

class Releases extends Fetch
{
	function getRequest() {
		$url = $this->url->mod([
			"path" => "/repos/" . $this->args["repo"] . "/releases",
			"query" => new QueryString([
				"page" => $this->getPage(),
			])
		], 0);
		echo $url."<br>";
		return new Request("GET", $url, [
			"Accept" => "application/vnd.github.v3+json",
			"Authorization" => "token " . $this->api->getToken(),
		]);
	}

	function getException($message, $code, $previous = null) {
		return new ReleasesFetchFailed($message, $code, $previous);
	}

	function getCacheKey() {
		return $this->api->getCacheKey(sprintf("releases:%s", $this->args["repo"]));
	}
}