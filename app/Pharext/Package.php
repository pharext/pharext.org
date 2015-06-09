<?php

namespace app\Pharext;

use pharext\Metadata;
use pharext\SourceDir;
use pharext\Task;

class Package
{
	private $file;

	function __construct($git_url, $tag_name, $pkg_name, $options) {
		$dir = (new Task\GitClone($git_url, $tag_name))->run();
		$src = !empty($options["pecl"])
			? new SourceDir\Pecl($dir)
			: new SourceDir\Git($dir);
		$meta = Metadata::all() + [
			"name" => $pkg_name,
			"release" => $tag_name,
			"license" => $src->getLicense(),
			"stub" => "pharext_installer.php",
			"type" => !empty($options["zend"]) ? "zend_extension" : "extension",
		];
		$this->file = (new Task\PharBuild($src, $meta))->run();
	}

	function __toString() {
		return (string) $this->file;
	}

	function getFile() {
		return $this->file;
	}
}
