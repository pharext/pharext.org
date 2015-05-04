<?php

namespace app\Controller\Github\Repo;

use app\Controller\Github;

class Hook extends Github
{
	function __invoke(array $args = null) {
		switch ($args["action"]) {
			case "add":
				$this->addHook($args["owner"], $args["name"]);
				break;
			
			case "del":
				$this->delHook($args["owner"], $args["name"]);
				break;
		}
	}
	
	function addHook($owner, $repo) {
		$this->github->createRepoHook("$owner/$repo", function($hook) use($owner, $repo) {
			if (($cache = $this->github->getCacheStorage())) {
				$cache->del($this->github->getCacheKey("hooks:$owner/$repo"));
			}
			if (($back = $this->app->getRequest()->getForm("returnback")) && isset($this->session->previous)) {
				$this->app->redirect($this->app->getBaseUrl()->mod($this->session->previous));
			} else {
				$this->app->redirect($this->app->getBaseUrl()->mod("./github/repo/$owner/$repo"));
			}
		})->send();
	}
	
	function delHook($owner, $repo) {
		$this->github->fetchRepo("$owner/$repo", function($repo) {
			$this->github->fetchHooks($repo->full_name, function($hooks) use($repo) {
				$repo->hooks = $hooks;
				if (($id = $this->checkRepoHook($repo))) {
					$this->github->deleteRepoHook($repo->full_name, $id, function() use($repo) {
						if (($cache = $this->github->getCacheStorage())) {
							$cache->del($this->github->getCacheKey("hooks:" . $repo->full_name));
						}
						if (($back = $this->app->getRequest()->getForm("returnback")) && isset($this->session->previous)) {
							$this->app->redirect($this->app->getBaseUrl()->mod($this->session->previous));
						} else {
							$this->app->redirect($this->app->getBaseUrl()->mod("./github/repo/" . $repo->full_name));
						}
					});
				}
			});
		})->send();
	}
}
