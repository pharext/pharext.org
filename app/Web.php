<?php

namespace app;

use http\Env\Request;
use http\Env\Response;

use FastRoute\Dispatcher;
use League\Plates;

class Web
{
	private $baseUrl;
	private $request;
	private $response;
	private $view;

	function __construct(BaseUrl $baseUrl, Request $request, Response $response, Plates\Engine $view) {
		$this->baseUrl = $baseUrl;
		$this->request = $request;
		$this->response = $response;
		$this->view = $view->addData(["location" => null]);
		ob_start($response);
	}

	function __invoke(Dispatcher $dispatcher) {
		$route = $dispatcher->dispatch($this->request->getRequestMethod(),
			$this->baseUrl->pathinfo($this->request->getRequestUrl()));

		switch ($route[0]) {
			case Dispatcher::NOT_FOUND:
				$this->response->setResponseCode(404);
				$this->response->getBody()->append($this->view->render("404"));
				break;

			case Dispatcher::METHOD_NOT_ALLOWED:
				$this->response->setResponseCode(405);
				$this->response->setHeader("Allowed", $route[1]);
				$this->response->getBody()->append($this->view->render("405"));
				break;

			case Dispatcher::FOUND:
				list(, $handler, $args) = $route;
				$handler(array_map("urldecode", $args));
				break;
		}

		$this->response->send();
	}

	function display($view, array $data = []) {
		$this->response->getBody()->append(
			$this->view->render($view, $data));
	}

	function redirect($url, $code = 302) {
		$this->response->setResponseCode($code);
		$this->response->setHeader("Location", $url);
	}

	function getBaseUrl() {
		return $this->baseUrl;
	}

	function getView() {
		return $this->view;
	}

	function getRequest() {
		return $this->request;
	}

	function getResponse() {
		return $this->response;
	}
}
