<?php

namespace app\Github\API\Repos;

use app\Github\API\Callback;
use app\Github\API\Hooks\HooksCallback;
use app\Github\API\Tags\TagsCallback;

class RepoCallback extends Callback
{
	function __invoke($repo, $links = null) {
		$this->api->listHooks($repo->full_name, new HooksCallback($this->api, $repo));
		$this->api->listTags($repo->full_name, 1, new TagsCallback($this->api, $repo));
		$this->api->listReleases($repo->full_name, 1, new ReleasesCallback($this->api, $repo));
		$this->api->readContents($repo->full_name, null, new ContentsCallback($this->api, $repo));;
	}
}
