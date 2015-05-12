<?php

namespace app;

require_once __DIR__."/config.php";

use http\Url;

$injector->share(Github\API::class)
	->delegate(Github\API::class, function() use($injector) {
		$config = $injector->make(Config::class);
		if (isset($config->github->hook->use_basic_auth)) {
			$basic = $config->github->hook->use_basic_auth;
			$config->github->hook->url = (string) new Url(
				$config->github->hook->url,
				$config->$basic->auth->toArray(),
				0);
		}
		// FIXME: configure through app.ini
		return new Github\API(
			 $config->github
			,new Github\Logger($config)
			,new Github\Storage\Session("gh-tokens")
		   #,new Github\Storage\Memcache("gh-cache")
			,new Github\Storage\Redis("gh-cache")
	   );
	});

