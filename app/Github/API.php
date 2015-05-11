<?php

namespace app\Github;

use app\Github\API;
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
		
		$call = new API\Users\ReadAuthToken($this, [
			"code" => $code,
			"client_id" => $this->config->client->id,
			"client_secret" => $this->config->client->secret,
		]);
		return $call($callback);
	}
	
	function fetchUser(callable $callback) {
		$call = new API\Users\ReadAuthUser($this);
		return $call($callback);
	}

	function fetchRepos($page, callable $callback) {
		$call = new API\Repos\ListRepos($this, compact("page"));
		return $call($callback);
	}

	function fetchRepo($repo, callable $callback) {
		$call = new API\Repos\ReadRepo($this, compact("repo"));
		return $call($callback);
	}

	function fetchHooks($repo, callable $callback) {
		$call = new API\Hooks\ListHooks($this, compact("repo"));
		return $call($callback);
	}

	function fetchReleases($repo, $page, callable $callback) {
		$call = new API\Releases\ListReleases($this, compact("repo", "page"));
		return $call($callback);
	}

	function fetchTags($repo, $page, callable $callback) {
		$call = new API\Tags\ListTags($this, compact("repo", "page"));
		return $call($callback);
	}
	
	function fetchContents($repo, $path, callable $callback) {
		$call = new API\Repos\ReadContents($this, compact("repo", "path"));
		return $call($callback);
	}
	
	function createRepoHook($repo, $conf, callable $callback) {
		$call = new API\Hooks\CreateHook($this, compact("repo", "conf"));
		return $call($callback);
	}
	
	function updateRepoHook($repo, $id, $conf, callable $callback) {
		$call = new API\Hooks\UpdateHook($this, compact("repo", "id", "conf"));
		return $call($callback);
	}
	
	function deleteRepoHook($repo, $id, callable $callback) {
		$call = new API\Hooks\DeleteHook($this, compact("repo", "id"));
		return $call($callback);
	}
	
	function createRelease($repo, $tag, callable $callback) {
		$call = new API\Releases\CreateRelease($this, compact("repo", "tag"));
		return $call($callback);
	}
	
	function createReleaseAsset($url, $asset, $type, callable $callback) {
		$call = new API\Releases\CreateReleaseAsset($this, compact("url", "asset", "type"));
		return $call($callback);
	}
}
