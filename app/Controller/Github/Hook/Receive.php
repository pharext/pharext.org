<?php

namespace app\Controller\Github\Hook;

use app\Controller;
use app\Github\API;
use app\Model\Accounts;
use app\Web;
use http\Params;
use pharext\Task;
use pharext\Metadata;
use pharext\SourceDir;

class Receive implements Controller
{
	private $app;
	private $github;
	private $accounts;
	
	function __construct(Web $app, API $github, Accounts $accounts) {
		$this->app = $app;
		$this->github = $github;
		$this->accounts = $accounts;
	}
	
	function __invoke(array $args = []) {
		$request = $this->app->getRequest();
		$response = $this->app->getResponse();
		
		if (!($sig = $request->getHeader("X-Hub-Signature")) || !($evt = $request->getHeader("X-Github-Event"))) {
			$response->setResponseCode(400);
			$response->setContentType("message/http");
			$response->getBody()->append($request);
		} else {
			$key = $this->github->getConfig()->client->secret;
			foreach ((new Params($sig))->params as $algo => $mac) {
				if ($mac["value"] !== hash_hmac($algo, $request->getBody(), $key)) {
					$response->setResponseCode(403);
					$response->getBody()->append("Invalid signature");
					return;
				}
			}
		}

		switch ($evt) {
			default:
				$response->setResponseCode(202);
				$response->getBody()->append("Not a configured event");
				break;
			case "ping";
				$response->setResponseCode(204);
				$response->setResponseStatus("PONG");
				break;
			case "create":
			case "release":
				if (($json = json_decode($request->getBody()))) {
					$this->$evt($json);
				} else {
					$response->setResponseCode(415);
					$response->setContentType($request->getHeader("Content-Type"));
					$response->getBody()->append($request->getBody());
				}
				break;
		}
	}
	
	private function setTokenForUser($login) {
		$relations = [
			$this->accounts->getTokens()->getRelation("accounts"),
			$this->accounts->getOwners()->getRelation("accounts")
		];
		$tokens = $this->accounts->getTokens()->with($relations, [
			"login=" => $login,
			"tokens.authority=" => "github",
		]);

		if (count($tokens)) {
			$this->github->setToken($tokens->current()->token->get());
		}
	}

	private function release($release) {
		$response = $this->app->getResponse();

		if ($release->action !== "published") {
			$response->setResponseCode(202);
			$response->getBody()->append("Not published");
		} elseif (!empty($release->release->assets)) {
			foreach ($release->release->assets as $asset) {
				if ($asset->content_type === "application/phar") {
					/* we've already uploaded the asset when we created the release */
					$response->setResponseCode(202);
					$response->getBody()->append("Already published");
					return;
				}
			}
		}
		
		$this->setTokenForUser($release->repository->owner->login);
		$this->github->uploadAssetForRelease($release->release, $release->repository, null, function($json) use($response) {
			$response->setResponseCode(201);
			$response->setHeader("Location", $json->url);
		})->send();
	}
	
	private function create($create) {
		$response = $this->app->getResponse();

		if ($create->ref_type !== "tag") {
			$response->setResponseCode(202);
			$response->getBody()->append("Not a tag");
			return;
		}
		
		$this->setTokenForUser($create->repository->owner->login);
		$this->github->createReleaseFromTag($create->repository, $create->ref, null, function($json) use($response) {
			$response->setResponseCode(201);
			$response->setHeader("Location", $json->url);
		})->send();
	}
	
	private function createReleaseAsset($release, $repo) {
		$hook = $this->github->checkRepoHook($repo);
		$phar = new Pharext\Package($repo->clone_url, $release->tag_name, $repo->name, $hook->config);
		return $phar->getFile();

		$dir = (new Task\GitClone($repo->clone_url, $release->tag_name))->run();
		if (!empty($hook->config->pecl)) {
			$src = new SoureDir\Pecl($dir);
		} else {
			$src = new SourceDir\Git($dir);
		}
		$meta = Metadata::all() + [
			"name" => $repo->name,
			"release" => $release->tag_name,
			"license" => $src->getLicense(),
			"stub" => "pharext_installer.php",
			"type" => !empty($hook->config->zend) ? "zend_extension" : "extension",
		];
		$file = (new Task\PharBuild($src, $meta))->run();
		return $file;
	}

}
