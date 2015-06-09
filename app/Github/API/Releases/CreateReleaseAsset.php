<?php

namespace app\Github\API\Releases;

use app\Github\API\Call;
use app\Github\Exception\RequestException;
use http\Client\Request;
use http\Client\Response;
use http\Message\Body;

class CreateReleaseAsset extends Call
{
	function request() {
		$body = new Body(fopen($this->args["asset"], "rb"));
		$request = new Request("POST", $this->args["url"], [
			"Authorization" => "token ". $this->api->getToken(),
			"Accept" => $this->config->api->accept,
			"Content-Type" => $this->args["type"],
		], $body);
		return $request;
	}
	
	function response(Response $response) {
		if ($response->getResponseCode() >= 400 || null === ($json = json_decode($response->getBody()))) {
			throw new RequestException($response);
		}
		return [$json];
	}
}
