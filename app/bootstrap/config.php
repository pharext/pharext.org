<?php

namespace app;

define("APP_ENVIRONMENT", getenv("APP_ENVIRONMENT") ?: "localhost");

$injector->share(Config::class)
	->define(Config::class, [
		"+array" => function($key, $injector) {
			return parse_ini_file(__DIR__."/../../config/app.ini", true);
		},
		":section" => APP_ENVIRONMENT
	])
	->prepare(Config::class, function($config, $injector) {
		$credentials = parse_ini_file(__DIR__."/../../config/credentials.ini", true);
		$config->addConfig(new Config($credentials, APP_ENVIRONMENT));
	});
