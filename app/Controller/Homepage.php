<?php

namespace app\Controller;

use app\Controller;

class Homepage implements Controller
{
	private $app;

	function __construct(\app\Web $app) {
		$this->app = $app;
	}

	function __invoke(array $args = null) {
		$this->app->getResponse()->getBody()->append(
			$this->app->getView()->render("pages/index")
		);
	}
}
