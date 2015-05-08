<?php

namespace app;

require_once __DIR__."/http.php";

use Auryn\Injector;
use http\Env\Request;

$injector->share(Session::class)
	->prepare(Session::class, function(Session $session, Injector $injector) {
		if (isset($session->current) && (!isset($session->previous) || strcmp($session->current, $session->previous))) {
			$session->previous = $session->current;
			$session->current = $injector->make(Request::class)->getRequestUrl();
		}
		$session->current = $injector->make(Request::class)->getRequestUrl();
	});
