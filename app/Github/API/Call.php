<?php

namespace app\Github\API;

use app\Github\API;
use http\QueryString;
use http\Url;
use merry\Config;

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
	 * Queue this call to the API client
	 */
	abstract function enqueue(callable $callback);
	
	/**
	 * @param API $api
	 * @param array $args
	 */
	function __construct(API $api, array $args = null) {
		$this->api = $api;
		$this->config = $this->api->getConfig();
		$this->url = new Url($this->config->api->url, null, 0);
		
		if ($args) {
			$this->args = $args;
		}
		if (isset($this->config->api->call->{$this}->args)) {
			$this->args += $this->config->api->call->{$this}->args->toArray();
		}
	}
	
	function __invoke(callable $callback) {
		if (empty($this->args["fresh"]) && ($cache = $this->api->getCacheStorage())) {
			$key = $this->getCacheKey();
			
			if ($cache->get($key, $cached)) {
				call_user_func_array($callback, $cached);
				return $this->api->getClient();
			}
		}
		
		$this->enqueue($callback);
		return $this;
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
	 * Call Client::send()
	 */
	function send() {
		return $this->api->getClient()->send();
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
		ksort($args);
		return sprintf("github:%s:%s:%s", $this->api->getToken(), $this, 
			new QueryString($args));
	}

	function readFromCache(array &$cached = null, &$ttl = null) {
		if (empty($this->args["fresh"]) && ($cache = $this->api->getCacheStorage())) {
			$key = $this->getCacheKey();
			return $cache->get($key, $cached, $ttl);
		}
		return false;
	}
	
	function saveToCache(array $fresh) {
		if (($cache = $this->api->getCacheStorage())) {
			if (isset($this->config->storage->cache->{$this}->ttl)) {
				$ttl = $this->config->storage->cache->{$this}->ttl;
			} else {
				$ttl = 0;
			}
			
			$key = $this->getCacheKey();
			$cache->set($key, $fresh, $ttl);
		}
	}
	
	function dropFromCache() {
		if (($cache = $this->api->getCacheStorage())) {
			$key = $this->getCacheKey();
			$cache->del($key);
		}
	}
}
