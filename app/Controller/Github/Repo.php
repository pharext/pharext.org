<?php

namespace app\Controller\Github;

use app\Controller\Github;
use app\Github\API\Repos\RepoCallback;

class Repo extends Github
{
	function __invoke(array $args = null) {
		extract($args);
		if ($this->checkToken()) {
			list($repo) = $this->github->readRepo("$owner/$name", new RepoCallback($this->github))->send();
			$hook = $this->github->checkRepoHook($repo);
			
			$this->app->getView()->addData(compact("owner", "name", "repo", "hook"));
			
			if (($modal = $this->app->getRequest()->getQuery("modal"))) {
				$action = $this->app->getRequest()->getQuery($modal);
				$this->app->getView()->addData(compact("modal", "action")); 
			}
			
			$this->app->display("github/repo");
		}
	}
}
