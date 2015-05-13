<?php

namespace app\Controller\Github;

use app\Controller\Github;

class Index extends Github
{
	function __invoke(array $args = null) {
		if ($this->checkToken()) {
			list($repos, $links) = $this->github->listRepos(
				$this->app->getRequest()->getQuery("page"), 
				new \app\Github\API\Repos\ReposCallback($this->github)
			)->send();
			$this->app->display("github/index", compact("repos", "links"));
		}
	}
}
