<?php

namespace app\Github\API;

use app\Controller;
use app\Github\API;
use app\Web;

class Hook implements Controller
{
	private $app;
	private $github;
	
	function __construct(Web $app, API $github) {
		$this->app = $app;
		$this->github = $github;
	}
	
	function __invoke(array $args = []) {
		$request = $this->app->getRequest();
		$response = $this->app->getResponse();
		
		if (!($sig = $request->getHeader("X-Github-Signature")) || !($evt = $request->getHeader("X-Github-Event"))) {
			$response->setResponseCode(400);
		} elseif ($sig !== hash_hmac("sha1", $request->getBody(), $this->app->getConfig()->github->client->secret)) {
			$response->setResponseCode(403);
		} elseif ($evt === "ping") {
			$response->setReponseStatus("PONG");
		} elseif ($evt !== "push") {
			$this->app->getResponse()->setResponseCode(204);
		} else {
			$push = json_decode($request->getBody());
		}
	}
}
