<?php

namespace app\Github\Create;

use app\Github\Create;
use app\Github\Exception\ReleaseAssetCreateFailed;
use http\Client\Request;

class ReleaseAsset extends Create
{
	function getRequest() {
		// FIXME: use uri_template extension
		$url = str_replace("{?name}", "?name=".urlencode(basename($this->args["asset"])), $this->args["url"]);
		$body = new \http\Message\Body(fopen($this->args["asset"], "rb"));
		$request = new Request("POST", $url, [
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
