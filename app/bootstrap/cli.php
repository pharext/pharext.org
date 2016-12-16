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
			[0, "command", "Command to run\n- ngrok: Run ngrok\n- initdb: Create database\n- gen-models: Generate pq\\Gatweay models", Args::SINGLE|Args::REQUIRED],
			["h", "help", "Show this help", Args::HALT],
		]
	]);
