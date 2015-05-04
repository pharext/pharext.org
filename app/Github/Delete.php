<?php

namespace app\Github;

use http\Header;
use http\Url;

abstract class Delete
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
	 * @var \http\Url
	 */
	protected $url;

	function __construct(API $api, array $args = []) {
		$this->api = $api;
		$this->config = $api->getConfig();
		$this->args = $args;
		$this->url = new Url("https://api.github.com/", null, 0);
	}

	function __toString() {
		$parts = explode("\\", get_class($this));
		return strtolower(end($parts));
	}
	
	abstract function getRequest();
	abstract function getException($message, $code, $previous = null);

	function __invoke(callable $callback) {
		$this->enqueue($callback);
		return $this->api->getClient();
	}

	protected function wrap(callable $callback) {
		return function($response) use($callback) {
			$rc = $response->getResponseCode();

			if ($rc !== 204) {
				if ($response->getHeader("Content-Type", Header::class)->match("application/json", Header::MATCH_WORD)) {
					$message = json_decode($response->getBody())->message;
				} else {
					$message = $response->getBody();
				}
				throw $this->getException($message, $rc);
			}
			
			$callback();

			return true;
		};
	}

	function enqueue(callable $callback) {
		$request = $this->getRequest();
		$wrapper = $this->wrap($callback);
		return $this->api->getClient()->enqueue($request, $wrapper);
	}
}