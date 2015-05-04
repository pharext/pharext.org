<?php

namespace app\Controller\Github;

use app\Controller\Github;

class Callback extends Github
{
	function __invoke(array $args = null) {
		if ($this->app->getRequest()->getQuery("error")) {
			$this->app->getView()->addData([
				"error" => $this->app->getRequest()->getQuery("error_description")
			]);
		} else {
			try {
				$this->github->fetchToken(
					$this->app->getRequest()->getQuery("code"),
					$this->app->getRequest()->getQuery("state"),
					function($json) {
						$this->github->setToken($json->access_token);
						$this->github->fetchUser(function($user) {
							$this->session->github = $user;
						});
				})->send();
				if (isset($this->session->returnto)) {
					$this->app->redirect($this->session->returnto);
				} else {
					$this->app->redirect(
						$this->app->getBaseUrl()->mod("./github"));
				}
			} catch (\app\Github\Exception $exception) {
				$this->app->getView()->addData(compact("exception"));
			}
		}
		$this->app->display("github/callback");
	}
}
