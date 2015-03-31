<?php

namespace app\Github\Fetch;

use app\Github\Exception\TagsFetchFailed;
use app\Github\Fetch;

use http\Client\Request;
use http\QueryString;

class Tags extends Fetch
{
	function getRequest() {
		$url = $this->url->mod([
			"path" => "/repos/" . $this->args["repo"] . "/tags",
			"query" => new QueryString([
				"page" => $this->getPage(),
			])
		], 0);
		return new Request("GET", $url, [
			"Accept" => "application/vnd.github.v3+json",
			"Authorization" => "token " . $this->api->getToken(),
		]);
	}

	function getException($message, $code, $previous = null) {
		return new TagsFetchFailed($message, $code, $previous);
	}

	function getCacheKey() {
		return $this->api->getCacheKey(sprintf("tags:%s", $this->args["repo"]));
	}
}