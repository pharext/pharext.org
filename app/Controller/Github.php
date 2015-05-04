<?php

namespace app\Controller;

use app\Controller;
use app\Github\API;
use app\Session;
use app\Web;

use http\QueryString;
use http\Url;

abstract class Github implements Controller
{
	/**
	 * @var \app\web
	 */
	protected $app;

	/**
	 * @var \app\Github\API
	 */
	protected $github;

	/**
	 * @var \app\Session
	 */
	protected $session;

	function __construct(Web $app, API $github, Session $session) {
		$this->app = $app;
		$this->github = $github;
		$this->session = $session;
		$this->app->getView()->addData(compact("session") + [
			"location" => "github", 
			"title" => "Github"
		]);
		$this->app->getView()->registerFunction("check", [$this, "checkRepoHook"]);
	}

	protected function checkToken() {
		if ($this->github->hasToken()) {
			return true;
		}
		$this->app->redirect($this->app->getBaseUrl()->mod([
			"path" => "github/signin",
			"query" => new QueryString(["returnto" => $this->session->current])
		]));
		return false;
	}

	/**
	 * Check if the pharext webhook is set for the repo and return its id
	 * @param object $repo
	 * @return int hook id
	 */
	function checkRepoHook($repo) {
		if ($repo->hooks) {
			foreach ($repo->hooks as $hook) {
				if ($hook->name === "web" && $hook->config->url === $this->github->getConfig()->hook->url) {
					return $hook->id;
				}
			}
		}
		return null;
	}

	function createLinkGenerator($links) {
		return function($which) use($links) {
			if (!isset($links[$which])) {
				if ($which !== "next" || !isset($links["last"])) {
					return null;
				} else {
					$which = "last";
				}
			}
			$url = new Url($links[$which], null, 0);
			$qry = new QueryString($url->query);
			return $qry->getInt("page", 1);
		};
	}

}
