<?php

namespace app\Github\API\Hooks;

use app\Github\API\Call;
use app\Github\Exception\RequestException;
use http\Client\Request;
use http\Client\Response;

class DeleteHook extends Call
{
	function request() {
		$url = $this->url->mod(uri_template("./repos/{+repo}/hooks{/id}", $this->args));
		$request = new Request("DELETE", $url, [
			"Authorization" => "token " . $this->api->getToken(),
			"Accept" => $this->config->api->accept,
		]);
		return $request;
	}
	
	function response(Response $response) {
		if ($response->getResponseCode() >= 400) {
			throw new RequestException($response);
		}
	}
}
