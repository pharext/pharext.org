<?php

namespace app\Github\API\Releases;

use app\Github\API\Call;
use app\Github\Exception\RequestException;
use app\Github\Links;
use http\Client\Request;
use http\Client\Response;

class ListReleaseAssets extends Call
{
	function request() {
		$url = $this->url->mod(uri_template("./repos/{+repo}/releases{/id}/assets", $this->args));
		$request = new Request("GET", $url, [
			"Authorization" => "token ". $this->api->getToken(),
			"Accept" => $this->config->api->accept,
		]);
		return $request;
	}
	
	function response(Response $response) {
		if ($response->getResponseCode() >= 400 || null === ($json = json_decode($response->getBody()))) {
			throw new RequestException($response);
		}
		$links = new Links($response->getHeader("Link"));
		$result = [$json, $links];
		$this->saveToCache($result);
		return $result;
	}
}
