<?php

namespace app\Github\Delete;

use app\Github\Delete;
use app\Github\Exception\WebhookDeleteFailed;
use http\Client\Request;

class Webhook extends Delete
{
	function getRequest() {
		$url = $this->url->mod("./repos/". $this->args["repo"] ."/hooks/". $this->args["id"]);
		$request = new Request("DELETE", $url, [
			"Accept" => "application/vnd.github.v3+json",
			"Authorization" => "token " . $this->api->getToken(),
		]);
		return $request;
	}
	
	function getException($message, $code, $previous = null) {
		return new WebhookDeleteFailed($message, $code, $previous);
	}
}
