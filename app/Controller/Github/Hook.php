<?php

namespace app\Controller\Github;

use app\Controller;
use app\Github\API;
use app\Model\Accounts;
use app\Web;
use http\Params;
use pharext\Task;
use pharext\SourceDir\Git;

require_once __DIR__."/../../../vendor/m6w6/pharext/src/pharext/Version.php";

class Hook implements Controller
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
	
	function release($release) {
		if ($release->action !== "published") {
			$response = $this->app->getResponse();
			
			$response->setResponseCode(202);
			$response->getBody()->append("Not published");
			return;
		}
		
		$this->uploadAssetForRelease($release->release, $release->repository);
	}
	
	private function uploadAssetForRelease($release, $repo) {
		$this->setTokenForUser($repo->owner->login);
		$asset = $this->createReleaseAsset($release, $repo);
		$this->github->createReleaseAsset($release->upload_url, $asset, "application/phar", function($json) {
			$response = $this->app->getResponse();
			$response->setResponseCode(201);
			$response->setHeader("Location", $json->url);
		})->send();
	}
	
	private function createReleaseAsset($release, $repo) {
		define("STDERR", fopen("/var/log/apache2/php_errors.log", "a"));
		$source = (new Task\GitClone($repo->clone_url, $release->tag_name))->run();
		$iterator = new Git($source);
		$meta = [
			"header" => sprintf("pharext v%s (c) Michael Wallner <mike@php.net>", \pharext\VERSION),
			"version" => \pharext\VERSION,
			"date" => date("Y-m-d"),
			"name" => $repo->name,
			"release" => $release->tag_name,
			"license" => @file_get_contents(current(glob($iterator->getBaseDir()."/LICENSE*"))),
			"stub" => "pharext_installer.php",
			"type" => false ? "zend_extension" : "extension",
		];
		$file = (new Task\PharBuild($iterator, $meta))->run();
		return $file;
	}
	
	function create($create) {
		if ($create->ref_type !== "tag") {
			$response = $this->app->getResponse();
			
			$response->setResponseCode(202);
			$response->getBody()->append("Not a tag");
			return;
		}
		
		$this->createReleaseFromTag($create->ref, $create->repository);
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
	
	private function createReleaseFromTag($tag, $repo) {
		$this->setTokenForUser($repo->owner->login);
		$this->github->createRelease($repo->full_name, $tag, function($json) {
			$response = $this->app->getResponse();
			$response->setResponseCode(201);
			$response->setHeader("Location", $json->url);
		})->send();
	}
}
