<?php

namespace app\Github;

use app\Github\API;
use app\Github\Storage;
use app\Github\Exception;
use app\Pharext;

use merry\Config;

use http\Client;
use http\QueryString;
use http\Url;

use Psr\Log\LoggerInterface;

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
	
	/**
	 * @var int
	 */
	private $maxAge;
	
	/**
	 * @var \Psr\Log\LoggerInterface;
	 */
	private $logger;

	function __construct(Config $config, LoggerInterface $logger, Storage $tokens = null, Storage $cache = null) {
		$this->logger = $logger;
		$this->config = $config;
		$this->client = new Client("curl", "github");
		$this->client->configure($config->http->configure->toArray());
		$this->client->attach(new ClientObserver($logger));
		$this->tokens = $tokens ?: new Storage\Session;
		$this->cache = $cache;
	}
	
	/**
	 * Set maximum acceptable age of cache items
	 * @param int $seconds
	 */
	function setMaxAge($seconds) {
		$this->maxAge = $seconds;
		return $this;
	}
	
	function getMaxAge() {
		return $this->maxAge;
	}
	
	function getLogger() {
		return $this->logger;
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
		$this->tokens->set("access_token", new Storage\Item(
			$token,
			$this->config->storage->token->ttl
		));
	}

	function getToken() {
		if ($this->tokens->get("access_token", $token, true)) {
			return $token->getValue();
		}
		if (isset($token)) {
			$this->logger->notice("Token expired", $token);
			throw new Exception\TokenExpired($token->getLTL());
		}
		throw new Exception\TokenNotSet;
	}

	function dropToken() {
		$this->tokens->del("access_token");
	}

	function getAuthUrl($callback_url) {
		$state = base64_encode(openssl_random_pseudo_bytes(24));
		$this->tokens->set("state", new Storage\Item($state, 5*60));
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

	function fetchToken($code, $state) {
		if (!$this->tokens->get("state", $orig_state, true)) {
			if (isset($orig_state)) {
				$this->logger->notice("State expired", $orig_state);
				throw new Exception\StateExpired($orig_state->getLTL());
			}
			throw new Exception\StateNotSet;
		}
		if ($state !== $orig_state->getValue()) {
			$this->logger->warning("State mismatch", compact("state", "orig_state"));
			throw new Exception\StateMismatch($orig_state->getValue(), $state);
		}
		
		$call = new API\Users\ReadAuthToken($this, [
			"code" => $code,
			"client_id" => $this->config->client->id,
			"client_secret" => $this->config->client->secret,
		]);
		return $call();
	}
	
	function readAuthUser() {
		$call = new API\Users\ReadAuthUser($this);
		return $call();
	}

	function listRepos($page, callable $callback) {
		$call = new API\Repos\ListRepos($this, compact("page"));
		return $call($callback);
	}

	function readRepo($repo, callable $callback) {
		$call = new API\Repos\ReadRepo($this, compact("repo"));
		return $call($callback);
	}

	/**
	 * Check if the pharext webhook is set for the repo and return it
	 * @param object $repo
	 * @return stdClass hook
	 */
	function checkRepoHook($repo) {
		if ($repo->hooks) {
			foreach ($repo->hooks as $hook) {
				if ($hook->name === "web" && $hook->config->url === $this->config->hook->url) {
					return $hook;
				}
			}
		}
		return null;
	}

	function listHooks($repo, callable $callback) {
		$call = new API\Hooks\ListHooks($this, compact("repo"));
		return $call($callback);
	}

	function listReleases($repo, $page, callable $callback) {
		$call = new API\Releases\ListReleases($this, compact("repo", "page"));
		return $call($callback);
	}

	function listTags($repo, $page, callable $callback) {
		$call = new API\Tags\ListTags($this, compact("repo", "page"));
		return $call($callback);
	}
	
	function readContents($repo, $path, callable $callback) {
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

	function publishRelease($repo, $id, $tag, callable $callback) {
		$call = new API\Releases\PublishRelease($this, compact("repo", "id", "tag"));
		return $call($callback);
	}

	function createReleaseAsset($url, $asset, $type, callable $callback) {
		$call = new API\Releases\CreateReleaseAsset($this, compact("url", "asset", "type"));
		return $call($callback);
	}
	
	function listReleaseAssets($repo, $id, callable $callback) {
		$call = new API\Releases\ListReleaseAssets($this, compact("repo", "id"));
		return $call($callback);
	}

	function uploadAssetForRelease($repo, $release, $config, callable $callback) {
		return $this->listHooks($repo->full_name, function($hooks) use($release, $repo, $config, $callback) {
			$repo->hooks = $hooks;
			$hook = $this->checkRepoHook($repo);
			$phar = new Pharext\Package($repo->clone_url, $release->tag_name, $repo->name, $config ?: $hook->config);
			$name = sprintf("%s-%s.ext.phar", $repo->name, $release->tag_name);
			$url = uri_template($release->upload_url, compact("name"));
			$this->createReleaseAsset($url, $phar, "application/phar", function($json) use($release, $repo, $callback) {
				if ($release->draft) {
					$this->publishRelease($repo->full_name, $release->id, $release->tag_name, function($json) use($callback) {
						$callback($json);
					});
				} else {
					$callback($json);
				}
			});
		});
	}

	function createReleaseFromTag($repo, $tag_name, $config, callable $callback) {
		return $this->createRelease($repo->full_name, $tag_name, function($json) use($repo, $callback) {
			$this->uploadAssetForRelease($repo, $json, $config, $callback);
		});
	}

}
