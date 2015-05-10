<?php

namespace app\Github\Create;

use app\Github\Create;
use app\Github\Exception\ReleaseAssetCreateFailed;
use http\Client\Request;

class ReleaseAsset extends Create
{
	function getRequest() {
		$body = new \http\Message\Body(fopen($this->args["asset"], "rb"));
		$request = new Request("POST", $this->args["url"], [
			"Accept" => "application/vnd.github.v3+json",
			"Content-Type" => $this->args["type"],
			"Authorization" => "token ". $this->api->getToken()
		], $body);
		return $request;
	}
	
	function getException($message, $code, $previous = null) {
		return new ReleaseAssetCreateFailed($message, $code, $previous);
	}
}
