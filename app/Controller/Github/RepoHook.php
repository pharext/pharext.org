<?php

namespace app\Controller\Github;

use app\Controller\Github;
use app\Github\API\Hooks\ListHooks;

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
		$this->github->createRepoHook("$owner/$repo", $hook_conf)->then(function($hook) use($owner, $repo) {
			$call = new ListHooks($this->github, ["repo" => "$owner/$repo", "fresh" => true]);
			$this->github->queue($call)->then(function() use($owner, $repo) {
				$this->redirectBack("$owner/$repo");
			});
		});
		$this->github->drain();
	}
	
	function updateHook($owner, $repo) {
		$this->github->readRepo("$owner/$repo")->then(function($result) {
			list($repo) = $result;
			$call = new ListHooks($this->github, ["repo" => $repo->full_name]);
			$this->github->queue($call)->then(function($result) use($repo, $call) {
				list($repo->hooks) = $result;
				if (($hook = $this->github->checkRepoHook($repo))) {
					$hook_conf = $this->app->getRequest()->getForm();
					$this->github->updateRepoHook($repo->full_name, $hook->id, $hook_conf)->then(function($hook_result) use($repo, $hook, $result, $call) {
						list($changed_hook) = $hook_result;
						foreach ($changed_hook as $key => $val) {
							$hook->$key = $val;
						}
						$call->saveToCache($result);
						$this->redirectBack($repo->full_name);
					});
				}
			});
		});
		$this->github->drain();
	}
	
	function delHook($owner, $repo) {
		$this->github->readRepo("$owner/$repo")->then(function($result) {
			list($repo) = $result;
			$call = new ListHooks($this->github, ["repo" => $repo->full_name]);
			$this->github->queue($call)->then(function($result) use($repo, $call) {
				list($repo->hooks) = $result;
				if (($hook = $this->github->checkRepoHook($repo))) {
					$this->github->deleteRepoHook($repo->full_name, $hook->id)->then(function() use($repo, $call) {
						$call->dropFromCache();
						$this->redirectBack($repo->full_name);
					});
				}
			});
		});
		$this->github->drain();
	}
	
	function redirectBack($repo) {
		if (($back = $this->app->getRequest()->getForm("returnback")) && isset($this->session->previous)) {
			$this->app->redirect($this->app->getBaseUrl()->mod($this->session->previous));
		} else {
			$this->app->redirect($this->app->getBaseUrl()->mod("./github/repo/" . $repo));
		}
	}
}
