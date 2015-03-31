<?php

namespace app\Controller\Github;

use app\Controller\Github;

class Repo extends Github
{
	function __invoke(array $args = null) {
		extract($args);
		if ($this->checkToken()) {
			try {
				$this->github->fetchRepo(
					"$owner/$name",
					[$this, "repoCallback"]
				)->send();
			} catch (\app\Github\Exception $exception) {
				$this->app->getView()->addData(compact("exception", "owner", "name"));
			}
			$this->app->display("github/repo");
		}
	}

	function repoCallback($repo, $links) {
		$this->app->getView()->addData(compact("repo"));
		settype($repo->tags, "object");
		$this->github->fetchTags($repo->full_name, 1, $this->createTagsCallback($repo));
		$this->github->fetchReleases($repo->full_name, 1, $this->createReleasesCallback($repo));
	}

	function createReleasesCallback($repo) {
		return function($releases, $links) use($repo) {
			foreach ($releases as $release) {
				$tag = $release->tag_name;
				settype($repo->tags->$tag, "object");
				$repo->tags->$tag->release = $release;
			}
		};
	}

	function createTagsCallback($repo) {
		return function($tags, $links) use ($repo) {
			foreach ($tags as $tag) {
				$name = $tag->name;
				settype($repo->tags->$name, "object");
				$repo->tags->$name->tag = $tag;
			}
		};
	}
}
