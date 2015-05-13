<?php

namespace app\Github\API;

use app\Github\API;
use app\Github\Storage\Item;
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
	 * @var array
	 */
	protected $result;
	
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
		if ($this->readFromCache($this->result)) {
			call_user_func_array($callback, $this->result);
		} else {
			$this->enqueue($callback);
		}
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
		$this->api->getClient()->send();
		return $this->result;
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
			return false;
		}
		if (null !== $this->api->getMaxAge() && $cached->getAge() > $this->api->getMaxAge()) {
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
		}
	}
	
	function dropFromCache() {
		if (($cache = $this->api->getCacheStorage())) {
			$key = $this->getCacheKey();
			$cache->del($key);
		}
	}
}
