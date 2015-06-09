<?php

namespace app\Controller\Github;

use app\Controller\Github;
use app\Github\API\Repos\RepoCallback;


class Release extends Github
{
	function __invoke(array $args = null) {
		extract($args);
		if ($this->checkToken()) {
			list($repo) = $this->github->readRepo("$owner/$name", function($repo, $links = null) {
				call_user_func(new RepoCallback($this->github), $repo, $links);

				$this->github->listReleases($repo->full_name, null, function($releases) use($repo) {
					$config = $this->app->getRequest()->getForm();
					foreach ($releases as $r) {
						if ($r->tag_name === $config->tag) {
							$this->github->uploadAssetForRelease($repo, $r, $config, function() use($repo) {
								$this->app->redirect($this->app->getBaseUrl()->mod("./github/" . $repo->full_name));
							});
							return;
						}
					}
					
					$this->github->createReleaseFromTag($repo, $tag, $config, function() use($repo) {
						$this->app->redirect($this->app->getBaseUrl()->mod("./github/" . $repo->full_name));
					});
				});
			})->send();
			
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
