<?php

namespace app\Github\API\Repos;

use app\Github\API\Callback;
use app\Github\API\Hooks\HooksCallback;
use app\Github\API\Tags\TagsCallback;

use React\Promise;

class RepoCallback extends Callback
{
	protected function exec($repo, $links = null) {
		return Promise\all([
			$repo,
			$this->api->listHooks($repo->full_name)->then(new HooksCallback($this->api, $repo)),
			$this->api->listTags($repo->full_name, 1)->then(new TagsCallback($this->api, $repo)),
			$this->api->listReleases($repo->full_name, 1)->then(new ReleasesCallback($this->api, $repo)),
			$this->api->readContents($repo->full_name)->then(new ContentsCallback($this->api, $repo)),
		]);
	}
}
