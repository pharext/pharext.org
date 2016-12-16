<?php

namespace app\Github\API\Repos;

use app\Github\API\Call;
use app\Github\Exception\RequestException;
use http\Client\Request;
use http\Client\Response;

class ReadRepo extends Call
{
	function request() {
		$url = $this->url->mod(uri_template("./repos/{+repo}", $this->args));
		return $this->requestUrl($url);
	}

	function requestUrl($url) {
		$request = new Request("GET", $url, [
			"Authorization" => "token " . $this->api->getToken(),
			"Accept" => $this->config->api->accept,
		]);
		return $request;
	}

	function response(Response $response) {
		if ($response->getResponseCode() >= 400 || null === ($json = json_decode($response->getBody()))) {
			throw new RequestException($response);
		}
		if ($response->getResponseCode() >= 300 && $response->getResponseCode() < 400) {
			$this->api->getClient()->enqueue(
				$this->requestUrl($json->url),
				function($response) {
					try {
						$this->deferred->resolve($this->response($response));
					} catch (\Exception $e) {
						$this->deferred->reject($e);
					}
					return true;
				}
			);
			return $this->deferred->promise();
		}
		$result = [$json];
		$this->saveToCache($result);
		return $result;
	}
}
