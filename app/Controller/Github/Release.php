<?php

namespace app\Controller\Github;

use app\Controller\Github;
use app\Github\API\Repos\RepoCallback;

class Release extends Github
{
	function __invoke(array $args = null) {
		extract($args);
		if ($this->checkToken()) {
			$this->github->readRepo("$owner/$name")->then(
				new RepoCallback($this->github)
			)->then(function($result) use(&$repo) {
				list($repo,,,$releases) = $result;
				$config = $this->app->getRequest()->getForm();
				
				foreach ($releases as $release) {
					if ($release->tag_name === $config["tag"]) {
						return $this->github->uploadAssetForRelease($repo, $release, $config);
					}
				}
				
				return $this->github->createReleaseFromTag($repo, $config["tag"], $config);
			})->done(function() use(&$repo) {
				$this->app->redirect($this->app->getBaseUrl()->mod("./github/repo/" . $repo->full_name));
			});
			
			$this->github->drain();
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
