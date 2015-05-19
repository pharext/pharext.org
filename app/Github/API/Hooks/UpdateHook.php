<?php

namespace app\Github\API\Hooks;

class UpdateHook extends \app\Github\API\Call
{
	function enqueue(callable $callback) {
		$url = $this->url->mod(uri_template("./repos/{+repo}/hooks{/id}", $this->args));
		$request = new \http\Client\Request("PATCH", $url, [
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
			"zend" => !empty($this->args["conf"]["zend"]),
			"pecl" => !empty($this->args["conf"]["pecl"]),
			"url" => $this->config->hook->url,
			"content_type" => $this->config->hook->content_type,
			"insecure_ssl" => $this->config->hook->insecure_ssl,
			"secret" => $this->config->client->secret, // FIXME: bad idea?
		];

		$request->getBody()->append(json_encode(compact("events", "config")));
		$this->api->getClient()->enqueue($request, function($response) use($callback) {
			if ($response->getResponseCode() >= 400 || null === ($json = json_decode($response->getBody()))) {
				throw new \app\Github\Exception\RequestException($response);
			}
			$this->result = [$json];
			$callback($json);
			return true;
		});
	}
}
