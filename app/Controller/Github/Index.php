<?php

namespace app\Controller\Github;

use app\Controller\Github;

use http\QueryString;

class Index extends Github
{
	function __invoke(array $args = null) {
		if ($this->checkToken()) {
			try {
				$this->github->fetchRepos(
					$this->app->getRequest()->getQuery("page"), 
					[$this, "reposCallback"]
				)->send();
			} catch (\app\Github\Exception $exception) {
				$this->view->addData(compact("exception"));
			}
			$this->app->display("github/index");
		}
	}

	function reposCallback($repos, $links) {
		$this->app->getView()->addData(compact("repos"));
		$this->app->getView()->registerFunction("link", $this->createLinkGenerator($links));

		foreach ($repos as $repo) {
			$this->github->fetchHooks($repo->full_name, function($hooks) use($repo) {
				$repo->hooks = $hooks;
			});
		}
	}
}
