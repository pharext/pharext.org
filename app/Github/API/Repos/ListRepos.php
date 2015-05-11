<?php

namespace app\Github\API\Repos;

use app\Github\API\Call;
use app\Github\Exception\RequestException;
use app\Github\Links;
use http\Client\Request;

class ListRepos extends Call
{
	function enqueue(callable $callback) {
		$url = $this->url->mod(uri_template("./user/repos{?page,per_page}", $this->args));
		$request = new Request("GET", $url, [
			"Authorization" => "token ". $this->api->getToken(),
			"Accept" => $this->config->api->accept,
		]);
		$this->api->getClient()->enqueue($request, function($response) use($callback) {
			if ($response->getResponseCode() >= 400 || null === ($json = json_decode($response->getBody()))) {
				throw new RequestException($response);
			}
			$links = new Links($response->getHeader("Link"));
			$this->saveToCache([$json, $links]);
			$callback($json, $links);
			return true;
		});
	}
}