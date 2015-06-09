<?php

namespace app\Github\API\Repos;

use app\Github\API;
use app\Github\API\Callback;

class ReleasesCallback extends Callback
{
	private $repo;
	
	function __construct(API $api, $repo) {
		parent::__construct($api);
		$this->repo = $repo;
	}
	
	protected function exec($json, $links = null) {
		settype($this->repo->tags, "object");
		foreach ($json as $release) {
			$tag = $release->tag_name;
			settype($this->repo->tags->$tag, "object");
			$this->repo->tags->$tag->release = $release;
			$this->api->listReleaseAssets(
				$this->repo->full_name, 
				$release->id
			)->done(function($result) use($release) {
				list($release->assets) = $result;
			});
		}
		return $json;
	}
}
