<?php

namespace app;

require_once __DIR__."/config.php";
require_once __DIR__."/github.php";

use Auryn\Injector;

use FastRoute\DataGenerator;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser;

use http\Env\Request;
use http\Env\Response;

$injector->share(Request::class);
$injector->share(Response::class);

$injector->share(RouteCollector::class)
	->prepare(RouteCollector::class, function($routes) use($injector) {
		$routes->addRoute("GET", "/reset", function(array $args = null) use($injector) {
			$injector->make(Session::class)->reset()->regenerateId();
			$injector->make(Web::class)->redirect($injector->make(BaseUrl::class));
		});
		$routes->addRoute("GET", "/session", function(array $args = null) use($injector) {
			$session = $injector->make(Session::class);
			$response = $injector->make(Response::class);
			$response->setContentType("text/plain");
			ob_start($response);
			var_dump($_SESSION, $session);
		});
		$routes->addRoute("GET", "/info", function(array $args = null) {
			phpinfo();
		});

		foreach (parse_ini_file(__DIR__."/../routes.ini", true) as $controller => $definition) {
			$factory = function(array $args = null) use($injector, $controller) {
				$handler = $injector->make("app\\Controller\\$controller");
				$handler($args);
			};
			foreach ($definition as $method => $locations) {
				foreach ($locations as $location) {
					$routes->addRoute($method, $location, $factory);
				}
			}
		}
	})
	->alias(RouteParser::class, RouteParser\Std::class)
	->alias(DataGenerator::class, DataGenerator\GroupCountBased::class);

$injector->share(Dispatcher::class)
	->alias(Dispatcher::class, Dispatcher\GroupCountBased::class)
	->delegate(Dispatcher\GroupCountBased::class, function($class, Injector $injector) {
		return new $class($injector->make(RouteCollector::class)->getData());
	});

$injector->prepare(Controller::class, function(Controller $controller, Injector $injector) {
	if (method_exists($controller, "setSession")) {
		$controller->setSession($injector->make(Session::class));
	}
});

$injector->share(Session::class)
	->prepare(Session::class, function(Session $session, Injector $injector) {
		if (isset($session->current) && (!isset($session->previous) || strcmp($session->current, $session->previous))) {
			$session->previous = $session->current;
			$session->current = $injector->make(Request::class)->getRequestUrl();
		}
		$session->current = $injector->make(Request::class)->getRequestUrl();
	});

$injector->share(BaseUrl::class);
$injector->share(Web::class);
