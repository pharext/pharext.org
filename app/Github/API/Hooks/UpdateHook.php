<?php

namespace app\Github\API\Hooks;

use app\Github\API\Call;
use app\Github\Exception\RequestException;
use http\Client\Request;
use http\Client\Response;

class UpdateHook extends Call
{
	function request() {
		$url = $this->url->mod(uri_template("./repos/{+repo}/hooks{/id}", $this->args));
		$request = new Request("PATCH", $url, [
			"Authorization" => "token ". $this->api->getToken(),
			"Accept" => $this->config->api->accept,
			"Content-Type" => "application/json",
		]);
		
		$events = [];
		if (!empty($this->args["conf"]["tag"])) {
			$events[] = "create";
		}
		if (!empty($this->args["conf"]["release"])) {
			$events[] = "release";
		}
		$config = [
			"zend" => (int)!empty($this->args["conf"]["zend"]),
			"pecl" => (int)!empty($this->args["conf"]["pecl"]),
			"url" => $this->config->hook->url,
			"content_type" => $this->config->hook->content_type,
			"insecure_ssl" => $this->config->hook->insecure_ssl,
			"secret" => $this->config->client->secret, // FIXME: bad idea?
		];

		$request->getBody()->append(json_encode(compact("events", "config")));
		return $request;
	}
	
	function response(Response $response) {
		if ($response->getResponseCode() >= 400 || null === ($json = json_decode($response->getBody()))) {
			throw new RequestException($response);
		}
		return [$json];
	}
}
