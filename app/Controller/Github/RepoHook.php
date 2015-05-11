<?php

namespace app\Controller\Github;

use app\Controller\Github;

class RepoHook extends Github
{
	function __invoke(array $args = null) {
		if ($this->checkToken()) {
			if ($this->app->getRequest()->getRequestMethod() != "POST") {
				// user had to re-authenticate, and was redirected here
				$this->app->redirect($this->app->getBaseUrl()->mod([
					"path" => "./github/repo/" . $args["owner"] ."/". $args["name"],
					"query" => "modal=hook&hook=" . $args["action"]
				]));
			} else {
				switch ($args["action"]) {
				case "upd":
					$this->updateHook($args["owner"], $args["name"]);
					break;
				
				case "add":
					$this->addHook($args["owner"], $args["name"]);
					break;

				case "del":
					$this->delHook($args["owner"], $args["name"]);
					break;
				}
			}
		}
	}
	
	function addHook($owner, $repo) {
		$hook_conf = $this->app->getRequest()->getForm();
		$this->github->createRepoHook("$owner/$repo", $hook_conf, function($hook) use($owner, $repo) {
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
				if (($hook = $this->checkRepoHook($repo))) {
					$this->github->deleteRepoHook($repo->full_name, $hook->id, function() use($repo) {
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
