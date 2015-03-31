<?php

namespace app;

require_once __DIR__."/../vendor/autoload.php";

use Auryn\Injector;
use Auryn\CachingReflector;
use Auryn\StandardReflector;

return function(array $modules) {
	$reflector = getenv("APP_ENVIRONMENT") == "production"
		? new CachingReflector
		: new StandardReflector
	;
	$injector = new Injector($reflector);

	foreach ($modules as $module) {
		require_once __DIR__."/bootstrap/$module.php";
	}

	return $injector;
};
