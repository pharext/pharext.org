<?php

namespace app\Controller\Github;

use app\Controller\Github;

class Index extends Github
{
	function __invoke(array $args = null) {
		if ($this->checkToken()) {
			$this->github->listRepos(
				$this->app->getRequest()->getQuery("page"), 
				[$this, "reposCallback"]
			)->send();
			$this->app->display("github/index");
		}
	}

	function reposCallback($repos, $links) {
		$this->app->getView()->addData(compact("repos", "links"));

		foreach ($repos as $repo) {
			$this->github->listHooks($repo->full_name, function($hooks) use($repo) {
				$repo->hooks = $hooks;
			});
		}
	}
}
