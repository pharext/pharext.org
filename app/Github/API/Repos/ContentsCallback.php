<?php

namespace app\Github\API\Repos;

use app\Github\API;
use app\Github\API\Callback;

class ContentsCallback extends Callback
{
	private $repo;
	
	function __construct(API $api, $repo) {
		parent::__construct($api);
		$this->repo = $repo;
	}
	
	protected function exec($json, $links = null) {
		foreach ($json as $entry) {
			if ($entry->type !== "file" || $entry->size <= 0) {
				continue;
			}
			if ($entry->name === "config.m4" || fnmatch("config?.m4", $entry->name)) {
				$this->repo->config_m4 = $entry->name;
			} elseif ($entry->name === "package.xml" || $entry->name === "package2.xml") {
				$this->repo->package_xml = $entry->name;
			} elseif ($entry->name === "pharext_package.php") {
				$this->repo->pharext_package_php = $entry->name;
			}
		}
		return $json;
	}
}
