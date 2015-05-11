<?php

namespace app\Github\API\Releases;

class ListReleaseAssets extends \app\Github\API\Call
{
	function enqueue(callable $callback) {
		$url = $this->url->mod(uri_template("./repos/{+repo}/releases{/release}/assets", $this->args));
		$request = new \http\Client\Request("GET", $url, [
			"Authorization" => "token ". $this->api->getToken(),
			"Accept" => $this->config->api->accept,
		]);
		$this->api->getClient()->enqueue($request, function($response) use($callback) {
			if ($response->getResponseCode() >= 400 || null === ($json = json_decode($response->getBody()))) {
				throw new \app\Github\Exception\RequestException($response);
			}
			$links = new Links($response->getHeader("Link"));
			$this->saveToCache([$json, $links]);
			$callback($json, $links);
			return true;
		});
	}
}
