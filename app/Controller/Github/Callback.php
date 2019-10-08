<?php

namespace app\Controller\Github;

use app\Controller\Github;
use app\Github\API;
use app\Model\Accounts;
use app\Session;
use app\Web;
use http\Cookie;

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
			$this->app->getView()->addData($this->app->getRequest()->getQuery()->toArray());
		} else {
			$this->validateUser();
			
			if (isset($this->session->returnto)) {
				$returnto = $this->session->returnto;
				unset($this->session->returnto);
				$this->app->redirect($returnto);
			} else {
				$this->app->redirect(
					$this->app->getBaseUrl()->mod(":./github"));
			}
		}
		$this->app->display("github/callback");
	}
	
	private function validateUser() {
		$this->github->fetchToken(
			$this->app->getRequest()->getQuery("code"),
			$this->app->getRequest()->getQuery("state")
		)->then(function($result) use (&$oauth) {
			list($oauth) = $result;
			$this->github->setToken($oauth->access_token);
			return $this->github->readAuthUser();
		})->done(function($result) use(&$oauth) {
			list($user) = $result;
			$this->login(...$this->persistUser($oauth, $user));
		});

		$this->github->drain();
	}
	
	private function persistUser($oauth, $user) {
		$tx = $this->accounts->getConnection()->startTransaction();

		if (($cookie = $this->app->getRequest()->getCookie("account"))) {
			$account = $this->accounts->find(["account=" => $cookie])->current();
		} elseif (!($account = $this->accounts->byOAuth("github", $oauth->access_token, $user->login))) {
			$account = $this->accounts->createOAuthAccount("github", $oauth->access_token, $user->login);
		}
		$token = $account->updateToken("github", $oauth->access_token, $oauth);
		$owner = $account->updateOwner("github", $user->login, $user);

		$tx->commit();

		return [$account, $token, $owner];
	}
}
