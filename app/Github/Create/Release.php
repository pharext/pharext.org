<?php

namespace app\Github\Create;

use app\Github\Create;
use app\Github\Exception\ReleaseCreateFailed;
use http\Client\Request;

class Release extends Create
{
	function getRequest() {
		$url = $this->url->mod("/repos/". $this->args["repo"] ."/releases");
		$request = new Request("POST", $url, [
			"Accept" => "application/vnd.github.v3+json",
			"Content-Type" => "application/json",
			"Authorization" => "token ". $this->api->getToken()
		]);
		$request->getBody()->append(json_encode([
			"tag_name" => $this->args["tag"]
		]));
		return $request;
	}
	
	function getException($message, $code, $previous = null) {
		return new ReleaseCreateFailed($message, $code, $previous);
	}
}
