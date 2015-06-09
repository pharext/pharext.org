<?php

namespace app\Github\API\Releases;

use app\Github\API\Call;
use app\Github\Exception\RequestException;
use http\Client\Request;
use http\Client\Response;

class CreateRelease extends Call
{
	function request() {
		$url = $this->url->mod("/repos/". $this->args["repo"] ."/releases");
		$request = new Request("POST", $url, [
			"Authorization" => "token ". $this->api->getToken(),
			"Accept" => $this->config->api->accept,
			"Content-Type" => "application/json",
		]);
		$request->getBody()->append(json_encode([
			"tag_name" => $this->args["tag"],
			"draft" => true,
		]));
		return $request;
	}
	
	function response(Response $response) {
		if ($response->getResponseCode() >= 400 || null === ($json = json_decode($response->getBody()))) {
			throw new RequestException($response);
		}
		return [$json];
	}
}
