<?php

namespace app\Github\API\Users;

use app\Github\API\Call;
use app\Github\Exception\RequestException;
use http\Client\Request;
use http\Client\Response;
use http\QueryString;

class ReadAuthToken extends Call
{
	protected function request() {
		$request = new Request("POST", "https://github.com/login/oauth/access_token", [
			"Accept" => "application/json",
		]);
		$request->getBody()->append(new QueryString($this->args));
		return $request;
	}
	
	protected function response(Response $response) {
		if ($response->getResponseCode() >= 400 || null === ($json = json_decode($response->getBody()))) {
			throw new RequestException($response);
		}
		return $json;
	}
	
	function getCacheKey() {
		return null;
	}
}
