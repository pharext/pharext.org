<?php

namespace app\Github\API\Tags;

use app\Github\API;
use app\Github\API\Callback;

class TagsCallback extends Callback
{
	private $repo;
	
	function __construct(API $api, $repo) {
		parent::__construct($api);
		$this->repo = $repo;
	}
	
	function __invoke($json, $links = null) {
		settype($this->repo->tags, "object");
		foreach ($json as $tag) {
			$name = $tag->name;
			settype($this->repo->tags->$name, "object");
			$this->repo->tags->$name->tag = $tag;
		}
	}
}
