<?php

namespace app\Controller\Github;

use app\Controller\Github;
use app\Github\API;
use app\Model\Accounts;
use app\Session;
use app\Web;

class Callback extends Github
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
		if ($this->app->getRequest()->getQuery("error")) {
			$this->app->getView()->addData([
				"error" => $this->app->getRequest()->getQuery("error_description")
			]);
		} else {
			$this->github->fetchToken(
				$this->app->getRequest()->getQuery("code"),
				$this->app->getRequest()->getQuery("state"),
				function($token) {
					$this->github->setToken($token->access_token);
					$this->github->readAuthUser($this->createUserCallback($token));
			})->send();
			if (isset($this->session->returnto)) {
				$returnto = $this->session->returnto;
				unset($this->session->returnto);
				$this->app->redirect($returnto);
			} else {
				$this->app->redirect(
					$this->app->getBaseUrl()->mod("./github"));
			}
		}
		$this->app->display("github/callback");
	}
	
	function createUserCallback($token) {
		return function($user) use($token) {
			$tx = $this->accounts->getConnection()->startTransaction();
			
			if (!($account = $this->accounts->byOAuth("github", $token->access_token, $user->login))) {
				$account = $this->accounts->createOAuthAccount("github", $token->access_token, $user->login);
			}
			$account->updateToken("github", $token->access_token, $token);
			$owner = $account->updateOwner("github", $user->login, $user);
			
			$tx->commit();
			
			$this->session->account = $account->account->get();
			$this->session->github = (object) $owner->export();
		};
	}
}
