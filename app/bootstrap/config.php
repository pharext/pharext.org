<?php

namespace app;

use merry\Config;

define("APP_ENVIRONMENT", getenv("APP_ENVIRONMENT") ?: "localhost");

$injector->share(Config::class)
	->define(Config::class, [
		":array" => parse_ini_file(__DIR__."/../config.ini", true),
		":section" => APP_ENVIRONMENT
	])
	->prepare(Config::class, function(Config $config) {
		$credentials = parse_ini_file(__DIR__."/../credentials.ini", true);
		foreach (new Config($credentials, APP_ENVIRONMENT) as $app => $creds) {
			/* one level down should suffice, i.e. $config->github->client = {id,secret,scope} */
			if ($creds instanceof Config) {
				foreach ($creds as $key => $val) {
					$config->$app->$key = $val;
				}
			} else {
				$config->$app = $creds;
			}
		}
	});
