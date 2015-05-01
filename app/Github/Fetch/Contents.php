<?php

namespace app\Github\Fetch;

use app\Github\Exception\ContentsFetchFailed;
use app\Github\Fetch;
use http\Client\Request;

class Contents extends Fetch
{
	function getRequest() {
		$url = $this->url->mod(sprintf("/repos/%s/contents/%s", 
			$this->args["repo"], $this->args["path"]));
		return new Request("GET", $url, [
			"Accept" => "application/vnd.github.v3+json",
			"Authorization" => "token " . $this->api->getToken()
		]);
	}
	
	function getException($message, $code, $previous = null) {
		return new ContentsFetchFailed($message, $code, $previous);
	}
	
	function getCacheKey() {
		return $this->api->getCacheKey(sprintf("contents:%s:%s",
			$this->args["repo"], $this->args["path"]));
	}
}
