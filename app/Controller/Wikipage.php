<?php

namespace app\Controller;

use app\Controller;
use app\Web;

class Wikipage implements Controller
{
	const WIKI_PATH = "../vendor/m6w6/pharext.wiki/";
	private $app;

	function __construct(Web $app) {
		$this->app = $app;
		$app->getView()->addData([
			"pages" => $this->wikiPages()
		]);
	}

	function __invoke(array $args = null) {
		$title = $args["page"];
		$this->app->getView()->addData(["title" => "About: $title"]);
		if ($title === "Packager hook") {
			$baseUrl = $this->app->getBaseUrl();
			$this->app->getView()->addData([
				"styles" => [$baseUrl->mod(":./highlight/styles/dark.css")],
				"scripts" => [
					$baseUrl->mod(":./highlight/highlight.pack.js"),
					"hljs.initHighlightingOnLoad();"
				]
			]);
		}
		$page = $this->wikiPath($args["page"]);
		$this->app->display("pages/wiki", compact("title", "page"));
	}

	function wikiPages() {
		return array_filter(array_map(function($s) {
				return strtr(basename($s, ".md"), "-", " ");
			}, scandir(self::WIKI_PATH)), function($s) {
				return $s{0} !== ".";
			});
	}
	
	function wikiPath($page) {
		$file = basename(strtr($page, " ", "-"), ".md") . ".md";
		return self::WIKI_PATH . $file;
	}
}