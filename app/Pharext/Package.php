<?php

namespace app\Pharext;

use pharext\Metadata;
use pharext\SourceDir;
use pharext\Task;

class Package
{
	private $source;
	private $file;
	private $name;
	private $release;
	private $zend;

	function __construct($git_url, $tag_name, $pkg_name, $options) {
		$dir = (new Task\GitClone($git_url, $tag_name))->run();
		$src = !empty($options["pecl"])
			? new SourceDir\Pecl($dir)
			: new SourceDir\Git($dir);

		/* setup defaults */
		$this->release = $tag_name;
		$this->name = $pkg_name;
		$this->zend = !empty($options["zend"]);

		/* override with package info from SourceDir */
		foreach ($src->getPackageInfo() as $key => $val) {
			switch ($key) {
				case "name":
				case "release":
				case "zend":
					$this->$key = $val;
					break;
			}
		}

		$this->source = $src;
	}

	function build() {
		$meta = Metadata::all() + [
			"name" => $this->name,
			"release" => $this->release,
			"license" => $this->source->getLicense(),
			"stub" => "pharext_installer.php",
			"type" => $this->zend ? "zend_extension" : "extension",
		];
		$this->file = (new Task\PharBuild($this->source, $meta))->run();
		
		return sprintf("%s-%s.ext.phar", $this->name, $this->release);
	}

	function __toString() {
		return (string) $this->file;
	}

	function getFile() {
		return $this->file;
	}

	function getName() {
		return $this->name;
	}

	function getRelease() {
		return $this->release;
	}
}
