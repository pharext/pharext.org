<?php

namespace app\Github\API\Hooks;

use app\Github\API\Call;
use app\Github\Exception\RequestException;
use http\Client\Request;

class DeleteHook extends Call
{
	function enqueue(callable $callback) {
		$url = $this->url->mod(uri_template("./repos/{+repo}/hooks{/id}", $this->args));
		$request = new Request("DELETE", $url, [
			"Authorization" => "token " . $this->api->getToken(),
			"Accept" => $this->config->api->accept,
		]);
		$this->api->getClient()->enqueue($request, function($response) use($callback) {
			if ($response->getResponseCode() >= 400) {
				throw new RequestException($response);
			}
			$callback();
			return true;
		});
	}
}
