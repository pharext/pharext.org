<?php

namespace app\Controller;

use app\Controller;
use app\Github\API;
use app\Model\Account;
use app\Model\Owner;
use app\Model\Token;
use app\Session;
use app\Web;
use http\Cookie;
use http\Header;
use http\QueryString;

abstract class Github implements Controller
{
	/**
	 * @var \app\web
	 */
	protected $app;

	/**
	 * @var API
	 */
	protected $github;

	/**
	 * @var Session
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

	protected function login(Account $account, Token $token, Owner $owner) {
		$auth = new Cookie;
		$auth->setCookie("account", $account->account->get());
		$auth->setFlags(Cookie::SECURE | Cookie::HTTPONLY);
		$auth->setPath($this->app->getBaseUrl()->path);
		$auth->setMaxAge(60*60*24);
		$this->app->getResponse()->setCookie($auth);

		$this->github->setToken($token->token->get());
		$this->session->account = $account->account->get();
		$this->session->github = (object) $owner->export();
	}

	protected function checkToken() {
		if ($this->github->hasToken()) {
			return true;
		}
		$this->app->redirect($this->app->getBaseUrl()->mod([
			"scheme" => null,
			"path" => "github/signin",
			"query" => new QueryString(["returnto" => $this->session->current])
		]));
		return false;
	}
}
