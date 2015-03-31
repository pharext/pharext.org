<?php

namespace app\Github\Fetch;

use app\Github\Exception\ReposFetchFailed;
use app\Github\Fetch;

use http\Client\Request;
use http\QueryString;

class Repos extends Fetch
{
	function getRequest() {
		$url = $this->url->mod([
			"path" => "/user/repos",
			"query" => new QueryString([
				"page" => $this->getPage()
			])
		]);
		return new Request("GET", $url, [
			"Accept" => "application/vnd.github.v3+json",
			"Authorization" => "token " . $this->api->getToken(),
		]);
	}

	function getException($message, $code, $previous = null) {
		return new ReposFetchFailed($message, $code, $previous);
	}

	function getCacheKey() {
		return $this->api->getCacheKey("repos", $this->page);
	}

	protected function wrap(callable $callback) {
		return parent::wrap(function($json, $links) use($callback) {
			if (($cache = $this->getStorage($ttl))) {
				foreach ($json as $repo) {
					$key = $this->api->getCacheKey(sprintf("repo:%s", $repo->full_name));
					$cache->set($key, [$repo, $links], $ttl);
				}
			}

			$callback($json, $links);

			return true;
		});
	}

}
