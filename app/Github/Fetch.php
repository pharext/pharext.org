<?php

namespace app\Github;

use http\Header;
use http\Params;
use http\Url;

abstract class Fetch
{
	/**
	 * @var \app\Github\API
	 */
	protected $api;

	/**
	 * @var \merry\Config
	 */
	protected $config;

	/**
	 * @var array
	 */
	protected $args;

	/**
	 * @var int
	 */
	protected $page = 1;

	/**
	 * @var \http\Url
	 */
	protected $url;

	function __construct(API $api, array $args = []) {
		$this->api = $api;
		$this->config = $api->getConfig();
		$this->args = $args;
		$this->url = new Url("https://api.github.com/", null, 0);
		if (isset($this->config->fetch->{$this}->per_page)) {
			$this->url->query = "per_page=" . $this->config->fetch->{$this}->per_page;
		}
	}

	function setPage($page) {
		$this->page = $page;
		return $this;
	}

	function getPage() {
		return $this->page;
	}

	function __toString() {
		$parts = explode("\\", get_class($this));
		return strtolower(end($parts));
	}
	
	function getStorage(&$ttl = null) {
		if (isset($this->config->storage->cache->{$this}->ttl)) {
			$ttl = $this->config->storage->cache->{$this}->ttl;
		}
		return $this->api->getCacheStorage();
	}

	abstract function getRequest();
	abstract function getException($message, $code, $previous = null);
	abstract function getCacheKey();

	function parseLinks(Header $header) {
		$params = new Params($header->value, ",", ";", "=",
			Params::PARSE_RFC5988 | Params::PARSE_ESCAPED);
		$links = [];
		foreach ($params->params as $link => $param) {
			// strip enclosing brackets
			$links[$param["arguments"]["rel"]] = $link;
		}
		return $links;
	}

	function __invoke(callable $callback) {
		$ttl = -1;
		$key = $this->getCacheKey();
		if (($cache = $this->api->getCacheStorage()) 
		&&	$cache->get($key, $cached, $ttl)) {
			call_user_func_array($callback, $cached);
		} else {
			$this->enqueue($callback);
		}
		return $this->api->getClient();
	}

	protected function wrap(callable $callback) {
		return function($response) use($callback) {
			$rc = $response->getResponseCode();

			if ($rc !== 200) {
				if ($response->getHeader("Content-Type", Header::class)->match("application/json", Header::MATCH_WORD)) {
					$message = json_decode($response->getBody())->message;
				} else {
					$message = $response->getBody();
				}
				throw $this->getException($message, $rc);
			}

			$json = json_decode($response->getBody());
			if (($link = $response->getHeader("Link", Header::class))) {
				$links = $this->parseLinks($link);
			} else {
				$links = [];
			}

			if (isset($json->error)) {
				throw $this->getException($json->error_description, $rc);
			} elseif (($cache = $this->getStorage($ttl))) {
				$cache->set($this->getCacheKey(), [$json, $links], $ttl);
			}

			$callback($json, $links);

			return true;
		};
	}

	function enqueue(callable $callback) {
		$request = $this->getRequest();
		$wrapper = $this->wrap($callback);
		return $this->api->getClient()->enqueue($request, $wrapper);
	}
}