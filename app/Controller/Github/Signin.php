<?php

namespace app\Controller\Github;

use app\Controller\Github;
use app\Github\API;
use app\Model\Accounts;
use app\Session;
use app\Web;

class Signin extends Github
{
	/**
	 * @var Accounts
	 */
	private $accounts;
	
	function __construct(Web $app, API $github, Session $session, Accounts $accounts) {
		parent::__construct($app, $github, $session);
		$this->accounts = $accounts;
	}
	
	function __invoke(array $args = null) {
		if (($cookie = $this->app->getRequest()->getCookie("account"))) {
			$accounts = $this->accounts->find(["account=" => $cookie]);
			if (count($accounts)) {
				$account = $accounts->current();
				$tokens = $account->allOf("tokens")->filter(function($token) {
					return $token->authority == "github";
				});
				if (count($tokens)) {
					$token = $tokens->current();
					$this->login($account, $token,
						$account->allOf("owners")->filter(function($owner) {
							return $owner->authority == "github";
						})->current()
					);
					if (($returnto = $this->app->getRequest()->getQuery("returnto"))) {
						$this->app->redirect($returnto);
					} else {
						$this->app->redirect($this->app->getBaseUrl()->mod("./github"));
					}
					return;
				}
			}
		}
		$callback = $this->app->getBaseUrl()->mod("./github/callback");
		$location = $this->github->getAuthUrl($callback);
		$this->app->redirect($location);
		if (($returnto = $this->app->getRequest()->getQuery("returnto"))) {
			$this->session->returnto = $returnto;
		}
	}
}
