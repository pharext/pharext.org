<?php

namespace app\Controller;

use app\Controller;
use app\Github\API;
use app\Session;
use app\Web;

use http\QueryString;
use http\Header;

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
		$this->app->getView()->registerFunction("check", [$github, "checkRepoHook"]);
		
		if (($header = $this->app->getRequest()->getHeader("Cache-Control", Header::class))) {
			$params = $header->getParams();
			if (!empty($params["no-cache"])) {
				$this->github->setMaxAge(0);
			} elseif (!empty($params["max-age"])) {
				$this->github->setMaxAge($params["max-age"]["value"]);
			}
		}
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
}
