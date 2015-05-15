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
		if (!file_exists("../config/maintenance")) {
			$route = $dispatcher->dispatch($this->request->getRequestMethod(),
				$this->baseUrl->pathinfo($this->request->getRequestUrl()));

			switch ($route[0]) {
				case Dispatcher::NOT_FOUND:
					$this->display(404, null, 404);
					break;

				case Dispatcher::METHOD_NOT_ALLOWED:
					$this->display(405, null, 405, ["Allowed" => $route[1]]);
					break;

				case Dispatcher::FOUND:
					list(, $handler, $args) = $route;
					try {
						$handler(array_map("urldecode", $args));
					} catch (\Exception $exception) {
						self::cleanBuffers();
						$this->display(500, compact("exception"), 500, ["X-Exception", get_class($exception)]);
					}
					break;
			}
		} else {
			$this->display(503, null, 503);
		}

		$this->response->send();
	}

	function display($view, array $data = null, $code = null, array $headers = []) {
		if (isset($code)) {
			$this->response->setResponseCode($code);
		}
		if ($headers) {
			$this->response->addHeaders($headers);
		}
		$this->response->getBody()->append(
			$this->view->render($view, (array) $data));
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
	
	static function cleanBuffers() {
		while (ob_get_level()) {
			if (!@ob_end_clean()) {
				break;
			}
		}
	}
}
