<?php

namespace app\Github\API\Releases;

use app\Github\API\Call;
use app\Github\Exception\RequestException;
use http\Client\Request;

class CreateRelease extends Call
{
	function enqueue(callable $callback) {
		$url = $this->url->mod("/repos/". $this->args["repo"] ."/releases");
		$request = new Request("POST", $url, [
			"Authorization" => "token ". $this->api->getToken(),
			"Accept" => $this->config->api->accept,
			"Content-Type" => "application/json",
		]);
		$request->getBody()->append(json_encode([
			"tag_name" => $this->args["tag"]
		]));
		$this->api->getClient()->enqueue($request, function($response) use($callback) {
			if ($response->getResponseCode() >= 400 || null === ($json = json_decode($response->getBody()))) {
				throw new RequestException($response);
			}
			$callback($json);
			return true;
		});
	}
}
