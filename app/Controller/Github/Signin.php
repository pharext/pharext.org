<?php

namespace app\Controller\Github;

use app\Controller\Github;

class Signin extends Github
{
	function __invoke(array $args = null) {
		$callback = $this->app->getBaseUrl()->mod("./github/callback");
		$location = $this->github->getAuthUrl($callback);
		$this->app->redirect($location);
		if (($returnto = $this->app->getRequest()->getQuery("returnto"))) {
			$this->session->returnto = $returnto;
		}
	}
}
