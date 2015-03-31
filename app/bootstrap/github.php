<?php

namespace app;

require_once __DIR__."/config.php";

use merry\Config;

$injector->share(Github\API::class)
	->delegate(Github\API::class, function() use($injector) {
		return new Github\API(
			$injector->make(Config::class)->github
		   ,new Github\Storage\Session("gh-tokens")
		   #,new Github\Storage\Memcache("gh-cache")
	   );
	});

