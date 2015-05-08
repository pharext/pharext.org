<?php

namespace app;

require_once __DIR__."/config.php";
require_once __DIR__."/http.php";
require_once __DIR__."/github.php";
require_once __DIR__."/router.php";
require_once __DIR__."/session.php";

use Auryn\Injector;

$injector->prepare(Controller::class, function(Controller $controller, Injector $injector) {
	if (method_exists($controller, "setSession")) {
		$controller->setSession($injector->make(Session::class));
	}
	if (method_exists($controller, "setDatabase")) {
		$controller->setDatabase($injector->make(Connection::class));
	}
});

$injector->share(BaseUrl::class);
$injector->share(Web::class);
