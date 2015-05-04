<?php

namespace app\Github\Create;

use app\Github\Create;
use app\Github\Exception\WebhookCreateFailed;
use http\Client\Request;

class Webhook extends Create
{
	function getRequest() {
		$url = $this->url->mod("./repos/". $this->args["repo"] ."/hooks");
		$request = new Request("POST", $url, [
			"Accept" => "application/vnd.github.v3+json",
			"Content-Type" => "application/json",
			"Authorization" => "token " . $this->api->getToken(),
		]);
		$request->getBody()->append(json_encode([
			"name" => "web",
			"config" => [
				"url" => $this->config->hook->url,
				"content_type" => $this->config->hook->content_type,
				"secret" => $this->config->client->secret, // FIXME: bad idea?
				"insecure_ssl" => false,
			]
		]));
		return $request;
	}
	
	function getException($message, $code, $previous = null) {
		return new WebhookCreateFailed($message, $code, $previous);
	}
}
