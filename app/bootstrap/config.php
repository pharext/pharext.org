<?php

namespace app;

define("APP_ENVIRONMENT", getenv("APP_ENVIRONMENT") ?: "localhost");

$injector->share(Config::class)
	->define(Config::class, [
		"+array" => function($key, $injector) {
			$settings = parse_ini_file(__DIR__."/../../config/app.ini", true);
			if (!$settings) {
				throw new \Exception("Could not parse settings");
			}
			return $settings;
		},
		":section" => APP_ENVIRONMENT
	])
	->prepare(Config::class, function($config, $injector) {
		$credentials = parse_ini_file(__DIR__."/../../config/credentials.ini", true);
		if (!$credentials) {
			throw new \Exception("Could not parse credentials");
		}
		$config->addConfig(new Config($credentials, APP_ENVIRONMENT));
	});
