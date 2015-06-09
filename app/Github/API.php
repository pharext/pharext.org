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
	
	/**
	 * Queued promises
	 * @var array
	 */
	private $queue;

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
		
		return $this->queue(new API\Users\ReadAuthToken($this, [
			"code" => $code,
			"client_id" => $this->config->client->id,
			"client_secret" => $this->config->client->secret,
		]));
	}
	
	function queue(API\Call $call) {
		return $this->queue[] = $call();
	}
	
	function drain() {
		$queue = $this->queue;
		$this->queue = array();
		$this->client->send();
		return $queue;
	}
	
	function readAuthUser() {
		return $this->queue(new API\Users\ReadAuthUser($this));
	}

	function listRepos($page) {
		return $this->queue(new API\Repos\ListRepos($this, compact("page")));
	}

	function readRepo($repo) {
		return $this->queue(new API\Repos\ReadRepo($this, compact("repo")));
	}

	/**
	 * Check if the pharext webhook is set for the repo and return it
	 * @param object $repo
	 * @return stdClass hook
	 */
	function checkRepoHook($repo) {
		if (!empty($repo->hooks)) {
			foreach ($repo->hooks as $hook) {
				if ($hook->name === "web" && $hook->config->url === $this->config->hook->url) {
					return $hook;
				}
			}
		}
		return null;
	}

	function listHooks($repo) {
		return $this->queue(new API\Hooks\ListHooks($this, compact("repo")));
	}

	function listReleases($repo, $page) {
		return $this->queue(new API\Releases\ListReleases($this, compact("repo", "page")));
	}

	function listTags($repo, $page) {
		return $this->queue(new API\Tags\ListTags($this, compact("repo", "page")));
	}
	
	function readContents($repo, $path = null) {
		return $this->queue(new API\Repos\ReadContents($this, compact("repo", "path")));
	}
	
	function createRepoHook($repo, $conf) {
		return $this->queue(new API\Hooks\CreateHook($this, compact("repo", "conf")));
	}
	
	function updateRepoHook($repo, $id, $conf) {
		return $this->queue(new API\Hooks\UpdateHook($this, compact("repo", "id", "conf")));
	}
	
	function deleteRepoHook($repo, $id) {
		return $this->queue(new API\Hooks\DeleteHook($this, compact("repo", "id")));
	}
	
	function createRelease($repo, $tag) {
		return $this->queue(new API\Releases\CreateRelease($this, compact("repo", "tag")));
	}

	function publishRelease($repo, $id, $tag) {
		return $this->queue(new API\Releases\PublishRelease($this, compact("repo", "id", "tag")));
	}

	function createReleaseAsset($url, $asset, $type) {
		return $this->queue(new API\Releases\CreateReleaseAsset($this, compact("url", "asset", "type")));
	}
	
	function listReleaseAssets($repo, $id) {
		return $this->queue(new API\Releases\ListReleaseAssets($this, compact("repo", "id")));
	}

	function uploadAssetForRelease($repo, $release, $config) {
		return $this->listHooks($repo->full_name)->then(function($result) use($release, $repo, $config) {
			list($repo->hooks) = $result;
			$hook = $this->checkRepoHook($repo);
			$phar = new Pharext\Package($repo->clone_url, $release->tag_name, $repo->name, $config ?: $hook->config);
			$name = sprintf("%s-%s.ext.phar", $repo->name, $release->tag_name);
			$url = uri_template($release->upload_url, compact("name"));
			$promise = $this->createReleaseAsset($url, $phar, "application/phar");
			if ($release->draft) {
				return $promise->then(function($result) use($release, $repo) {
					return $this->publishRelease($repo->full_name, $release->id, $release->tag_name);
				});
			}
			return $promise;
		});
	}

	function createReleaseFromTag($repo, $tag_name, $config) {
		return $this->createRelease($repo->full_name, $tag_name)->then(function($result) use($repo, $config) {
			list($release) = $result;
			return $this->uploadAssetForRelease($repo, $release, $config);
		});
	}

}
