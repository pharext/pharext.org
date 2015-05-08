<?php

namespace app;

use Auryn\Injector;

use FastRoute\DataGenerator;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser;

use http\Env\Response;

$injector->share(RouteCollector::class)
	->prepare(RouteCollector::class, function($routes) use($injector) {
		$routes->addRoute("GET", "/reset", function(array $args = null) use($injector) {
			$injector->make(Session::class)->reset()->regenerateId();
			$injector->make(Web::class)->redirect($injector->make(BaseUrl::class));
		});
		$routes->addRoute("GET", "/session", function(array $args = null) use($injector) {
			$session = $injector->make(Session::class);
			$response = $injector->make(Response::class);
			if (!(extension_loaded("xdebug") && ini_get("xdebug.overload_var_dump") && ini_get("html_errors"))) {
				$response->setContentType("text/plain");
			}
			ob_start($response);
			var_dump($_SESSION, $session);
		});
		$routes->addRoute("GET", "/info", function(array $args = null) {
			phpinfo();
		});

		foreach (parse_ini_file(__DIR__."/../../config/routes.ini", true) as $controller => $definition) {
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

