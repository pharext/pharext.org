<?php

namespace app\Controller\Github;

use app\Controller\Github;
use app\Github\API\Hooks\ListHooks;

class RepoHook extends Github
{
	function __invoke(array $args = null) {
		if (!$this->checkToken()) {
			return;
		}
		if ($this->app->getRequest()->getRequestMethod() != "POST") {
			// user had to re-authenticate, and was redirected here
			$this->app->redirect($this->app->getBaseUrl()->mod([
				"path" => "./github/repo/" . $args["owner"] ."/". $args["name"],
				"query" => "modal=hook&hook=" . $args["action"]
			]));
		} else {
			$this->changeHook($args)->done(function() use($args) {
				$this->redirectBack($args["owner"]."/".$args["name"]);
			});
			$this->github->drain();
		}
	}

	function changeHook($args) {
		switch ($args["action"]) {
		case "upd":
			return $this->updateHook($args["owner"] ."/". $args["name"]);
		case "add":
			return $this->addHook($args["owner"] ."/". $args["name"]);
		case "del":
			return $this->delHook($args["owner"] ."/". $args["name"]);
		default:
			throw new \Exception("Unknown action ".$args["action"]);
		}
	}
	
	function addHook($repo_name) {
		$hook_conf = $this->app->getRequest()->getForm();
		$listhooks = new ListHooks($this->github, ["repo" => $repo_name]);
		return $this->github->createRepoHook($repo_name, $hook_conf)->then(function() use($listhooks) {
			$listhooks->dropFromCache();
		});
	}
	
	function updateHook($repo_name) {
		$listhooks = new ListHooks($this->github, ["repo" => $repo_name]);
		return $this->github->queue($listhooks)->then(function($result) use($repo_name) {
			list($hooks) = $result;

			if (!($hook = $this->github->checkHook($hooks))) {
				throw new \Exception("Hook is not set");
			}

			return $this->github->updateRepoHook($repo_name, $hook->id, $this->app->getRequest()->getForm());
		})->then(function() use($listhooks) {
			$listhooks->dropFromCache();
		});
	}
	
	function delHook($repo_name) {
		$listhooks = new ListHooks($this->github, ["repo" => $repo_name]);
		return $this->github->queue($listhooks)->then(function($result) use($repo_name) {
			list($hooks) = $result;

			if (!($hook = $this->github->checkHook($hooks))) {
				throw new \Exception("Hook is not set");
			}
			
			return $this->github->deleteRepoHook($repo_name, $hook->id);
		})->then(function() use($listhooks) {
			$listhooks->dropFromCache();
		});
	}
	
	function redirectBack($repo) {
		if (($back = $this->app->getRequest()->getForm("returnback")) && isset($this->session->previous)) {
			$this->app->redirect($this->app->getBaseUrl()->mod($this->session->previous));
		} else {
			$this->app->redirect($this->app->getBaseUrl()->mod(":./github/repo/" . $repo));
		}
	}
}
