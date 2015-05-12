<?php

namespace app\Github\API\Users;

use app\Github\API\Call;
use app\Github\Exception\RequestException;
use http\Client\Request;
use http\QueryString;

class ReadAuthToken extends Call
{
	function enqueue(callable $callback) {
		$request = new Request("POST", "https://github.com/login/oauth/access_token", [
			"Accept" => "application/json",
		]);
		$request->getBody()->append(new QueryString($this->args));
		$this->api->getClient()->enqueue($request, function($response) use($callback) {
			if ($response->getResponseCode() >= 400 || null === ($json = json_decode($response->getBody()))) {
				throw new RequestException($response);
			}
			$callback($json);
			return true;
		});
	}
	
	function getCacheKey() {
		return null;
	}
}
