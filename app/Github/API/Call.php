<?php

namespace app\Github\API;

use app\Github\API;
use app\Github\Storage\Item;
use http\Client\Response;
use http\QueryString;
use http\Url;
use merry\Config;

use React\Promise;

abstract class Call
{
	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var \app\Gituhub\API
	 */
	protected $api;
	
	/**
	 * @var array
	 */
	protected $args = [];
	
	/**
	 * @var \http\Url
	 */
	protected $url;
	
	/**
	 * @var QueryString
	 */
	protected $query;
	
	/**
	 * @var \React\Promise\Deferred
	 */
	protected $deferred;
	
	/**
	 * @return Request
	 */
	abstract protected function request();
	
	/**
	 * @return array
	 */
	abstract protected function response(Response $response);
	
	/**
	 * @param API $api
	 * @param array $args
	 */
	function __construct(API $api, array $args = null) {
		$this->api = $api;
		$this->config = $this->api->getConfig();
		$this->url = new Url($this->config->api->url, null, 0);
		$this->deferred = new Promise\Deferred;
		
		if ($args) {
			$this->args = $args;
		}
		if (isset($this->config->api->call->{$this}->args)) {
			$this->args += $this->config->api->call->{$this}->args->toArray();
		}
	}
	
	/**
	 * @return \React\Promise\Promise
	 */
	function __invoke() {
		if ($this->readFromCache($this->result)) {
			return new Promise\FulfilledPromise($this->result);
		} else {
			$this->api->getClient()->enqueue(
				$this->request(),
				function($response) {
					try {
						$result = $this->response($response);
						if (!($result instanceof Promise\PromiseInterface)) {
							$this->deferred->resolve($result);
						}
					} catch (\Exception $e) {
						$this->deferred->reject($e);
					}
					return true;
				}
			);
			return $this->deferred->promise();
		}
	}
	
	/**
	 * Get type of call
	 * @return string
	 */
	function __toString() {
		$parts = explode("\\", get_class($this));
		return strtolower(end($parts));
	}
	
	/**
	 * Get associated cache storage
	 * @param int $ttl out param of configure ttl
	 * @return Storage
	 */
	function getCache(&$ttl = null) {
		if (isset($this->config->storage->cache->{$this}->ttl)) {
			$ttl = $this->config->storage->cache->{$this}->ttl;
		}
		return $this->api->getCacheStorage();
	}
	
	function getCacheKey() {
		$args = $this->args;
		unset($args["fresh"]);
		if (isset($args["page"]) && !strcmp($args["page"], "1")) {
			unset($args["page"]);
		}
		ksort($args);
		return sprintf("%s:%s:%s", $this->api->getToken(), $this, 
			new QueryString($args));
	}

	function readFromCache(array &$value = null) {
		if (!empty($this->args["fresh"])) {
			return false;
		}
		if (!($cache = $this->api->getCacheStorage())) {
			return false;
		}
		if (!strlen($key = $this->getCacheKey())) {
			return false;
		}
		if (!$cache->get($key, $cached)) {
			if ($cached) {
				$this->api->getLogger()->debug(
					sprintf("Cache-Stale: $this [TTL=%d]", $cached->getTTL()),
					$this->args);
			} else {
				$this->api->getLogger()->debug("Cache-Miss: $this", $this->args);
			}
			return false;
		}
		if (null !== $this->api->getMaxAge() && $cached->getAge() > $this->api->getMaxAge()) {
			$this->api->getLogger()->debug("Cache-Refresh: $this", $this->args);
			return false;
		}
		$this->api->getLogger()->debug("Cache-Hit: $this", $this->args);
		$value = $cached->getValue();
		return true;
	}
	
	function saveToCache(array $fresh) {
		if (($cache = $this->api->getCacheStorage())) {
			if (isset($this->config->storage->cache->{$this}->ttl)) {
				$ttl = $this->config->storage->cache->{$this}->ttl;
			} else {
				$ttl = null;
			}
			
			$key = $this->getCacheKey();
			$cache->set($key, new Item($fresh, $ttl));
			$this->api->getLogger()->debug("Cache-Push: $this", $this->args);
		}
	}
	
	function dropFromCache() {
		if (($cache = $this->api->getCacheStorage())) {
			$key = $this->getCacheKey();
			$cache->del($key);
			$this->api->getLogger()->debug("Cache-Drop: $this", $this->args);
		}
	}
}
