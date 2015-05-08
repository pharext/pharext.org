<?php

namespace app;

use pharext\Cli\Args;

require_once __DIR__."/config.php";
require_once __DIR__."/github.php";

/* @var $injector \Auryn\Injector */

$injector->share(Cli::class);

$injector->share(Args::class)
	->define(Args::class, [
		":spec" => [
			[null, "ngrok", "Run ngrok", Args::SINGLE],
			[null, "initdb", "Create database", Args::SINGLE],
			[null, "gen-models", "Generate pq\\Gateway models", Args::SINGLE],
			["h", "help", "Show this help", Args::HALT],
		]
	]);
