<?php

namespace app\Github;

use app\Github\Storage;
use app\Github\Exception;

use merry\Config;

use http\Client;
use http\QueryString;
use http\Url;

class API
{
	/**
	 * @var Client
	 */
	private $client;

	/**
	 * @var Storage
	 */
	private $tokens;

	/**
	 * @var Storage
	 */
	private $cache;

	/**
	 * @var merry\Config
	 */
	private $config;

	function __construct(Config $config, Storage $tokens = null, Storage $cache = null) {
		$this->config = $config;
		$this->client = new Client;
		$this->tokens = $tokens ?: new Storage\Session;
		$this->cache = $cache;
	}

	function getConfig() {
		return $this->config;
	}
	
	function getClient() {
		return $this->client;
	}

	function getTokenStorage() {
		return $this->tokens;
	}

	function getCacheStorage() {
		return $this->cache;
	}

	function getCacheKey($ident, $page = null) {
		return sprintf("%s:%s:%s", $this->getToken(), $ident, $page ?: 1);
	}

	function hasToken() {
		return $this->tokens->get("access_token");
	}

	function setToken($token) {
		$this->tokens->set("access_token", $token,
			$this->config->storage->token->ttl);
	}

	function getToken() {
		if ($this->tokens->get("access_token", $token, $ttl, true)) {
			return $token;
		}
		if (isset($ttl)) {
			throw new Exception\TokenExpired($ttl);
		}
		throw new Exception\TokenNotSet;
	}

	function dropToken() {
		$this->tokens->del("access_token");
	}

	function getAuthUrl($callback_url) {
		$state = base64_encode(openssl_random_pseudo_bytes(24));
		$this->tokens->set("state", $state, 5*60);
		$param = [
			"state" => $state,
			"client_id" => $this->config->client->id,
			"scope" => $this->config->client->scope,
			"redirect_uri" => $callback_url,
		];
		return new Url("https://github.com/login/oauth/authorize", [
			"query" => new QueryString($param)
		], 0);
	}

	function fetchToken($code, $state, callable $callback) {
		if (!$this->tokens->get("state", $orig_state, $ttl, true)) {
			if (isset($ttl)) {
				throw new Exception\StateExpired($ttl);
			}
			throw new Exception\StateNotSet;
		}
		if ($state !== $orig_state) {
			throw new Exception\StateMismatch($orig_state, $state);
		}

		$fetch = new Fetch\Token($this, compact("code") + $this->config->client->toArray());
		return $fetch($callback);
	}
	
	function fetchUser(callable $callback) {
		$fetch = new Fetch\User($this);
		return $fetch($callback);
	}

	function fetchRepos($page, callable $callback) {
		$fetch = new Fetch\Repos($this);
		$fetch->setPage($page);
		return $fetch($callback);
	}

	function fetchRepo($repo, callable $callback) {
		$fetch = new Fetch\Repo($this, compact("repo"));
		return $fetch($callback);
	}

	function fetchHooks($repo, callable $callback) {
		$fetch = new Fetch\Hooks($this, compact("repo"));
		return $fetch($callback);
	}

	function fetchReleases($repo, $page, callable $callback) {
		$fetch = new Fetch\Releases($this, compact("repo"));
		$fetch->setPage($page);
		return $fetch($callback);
	}

	function fetchTags($repo, $page, callable $callback) {
		$fetch = new Fetch\Tags($this, compact("repo"));
		$fetch->setPage($page);
		return $fetch($callback);
	}
	
	function fetchContents($repo, $path, callable $callback) {
		$fetch = new Fetch\Contents($this, compact("repo", "path"));
		return $fetch($callback);
	}
	
	function createRepoHook($repo, callable $callback) {
		$create = new Create\Webhook($this, compact("repo"));
		return $create($callback);
	}
	
	function deleteRepoHook($repo, $id, callable $callback) {
		$delete = new Delete\Webhook($this, compact("repo", "id"));
		return $delete($callback);
	}
}
