<?php

namespace app\Controller\Github;

use app\Controller\Github;
use app\Github\API\Repos\ReposCallback;

class Index extends Github
{
	function __invoke(array $args = null) {
		if ($this->checkToken()) {
			$this->github->listRepos(
				$this->app->getRequest()->getQuery("page")
			)->then(
				new ReposCallback($this->github)
			)->done(function($results) {
				list(list($repos, $links)) = $results;
				$this->app->display("github/index", compact("repos", "links"));
			});
			$this->github->drain();
		}
	}
}
