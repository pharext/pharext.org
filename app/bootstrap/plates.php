<?php

namespace app;

require_once __DIR__."/config.php";
require_once __DIR__."/web.php";

use League\Plates;

use http\Env\Request;
use http\Env\Response;

$injector->share(Plates\Engine::class)
	->define(Plates\Engine::class, [
		":directory" => __DIR__."/../views",
		":fileExtension" => "phtml"
	])
	->prepare(Plates\Engine::class, function(Plates\Engine $view) use($injector) {
		$view->addData([
			"config" => $injector->make(Config::class),
			"baseUrl" => $injector->make(BaseUrl::class),
			"request" => $injector->make(Request::class),
			"response" => $injector->make(Response::class),
		]);
		$view->registerFunction("shorten", function($text) {
			if (strlen($text) < 78) {
				return $text;
			}
			return current(explode("\n", wordwrap($text)))."…";
		});
		$view->registerFunction("utc", function($d) {
			return date_create($d)->setTimeZone(new \DateTimeZone("UTC"));
		});
		$view->registerFunction("md", function($string, $file = false) {
			if ($file) {
				$md = \MarkdownDocument::createFromStream($string);
			} else {
				$md = \MarkdownDocument::createFromString($string);
			}
			$md->compile(\MarkdownDocument::AUTOLINK);
			return $md->getHtml();
		});
	});

