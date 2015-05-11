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
		
		$request->getBody()->append(json_encode(compact("events")));
		$this->api->getClient()->enqueue($request, function($response) use($callback) {
			if ($response->getResponseCode() >= 400 || null === ($json = json_decode($response->getBody()))) {
				throw new \app\Github\Exception\RequestException($response);
			}
			$callback($json);
			return true;
		});
	}
}
