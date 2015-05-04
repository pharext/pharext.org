<?php

namespace app\Github\Fetch;

use app\Github\Exeption\UserFetchFailed;
use app\Github\Fetch;
use http\Client\Request;

class User extends Fetch
{
	function getRequest() {
		$url = $this->url->mod("/user");
		return new Request("GET", $url, [
			"Accept" => "application/vnd.github.v3+json",
			"Authorization" => "token " . $this->api->getToken()
		]);
	}
	
	function getException($message, $code, $previous = null) {
		return new UserFetchFailed($message, $code, $previous);
	}
	
	function getCacheKey() {
		return $this->api->getCacheKey("user");
	}
}
