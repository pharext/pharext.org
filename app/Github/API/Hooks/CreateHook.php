<?php

namespace app\Github\API\Hooks;

use app\Github\API\Call;
use app\Github\Exception\RequestException;
use http\Client\Request;

class CreateHook extends Call
{
	function enqueue(callable $callback) {
		$url = $this->url->mod("./repos/". $this->args["repo"] ."/hooks");
		$request = new Request("POST", $url, [
			"Authorization" => "token " . $this->api->getToken(),
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
		
		$request->getBody()->append(json_encode([
			"name" => "web",
			"events" => $events,
			"config" => [
				"zend" => (int)!empty($this->args["conf"]["zend"]),
				"pecl" => (int)!empty($this->args["conf"]["pecl"]),
				"url" => $this->config->hook->url,
				"content_type" => $this->config->hook->content_type,
				"insecure_ssl" => $this->config->hook->insecure_ssl,
				"secret" => $this->config->client->secret, // FIXME: bad idea?
			]
		]));
		
		$this->api->getClient()->enqueue($request, function($response) use($callback) {
			if ($response->getResponseCode() >= 400 || null === ($json = json_decode($response->getBody()))) {
				throw new RequestException($response);
			}
			$this->result = [$json];
			$callback($json);
			return true;
		});
	}
}
