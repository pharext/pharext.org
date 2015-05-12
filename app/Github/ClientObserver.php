<?php

namespace app\Github;

use SplObserver;
use SplSubject;

use http\Client\Request;

use Psr\Log\LoggerInterface;

class ClientObserver implements SplObserver
{
	private $logger;
	
	function __construct(LoggerInterface $logger) {
		$this->logger = $logger;
	}
	
	function update(SplSubject $client, Request $request = null, $progress = null) {
		switch ($progress->info) {
			case "start":
				if (!$progress->started) {
					$message = sprintf("API-Shot: start %s %s", $request->getRequestMethod(), $request->getRequestUrl());
					$this->logger->debug($message);
				}
				break;
			case "finished":
				$response = $client->getResponse($request);
				$message = sprintf("API-Shot: finished [%d] %s %s", $response->getResponseCode(), $request->getRequestMethod(), $request->getRequestUrl());
				if ($response->getResponseCode() >= 400 || $response->getTransferInfo("error")) {
					$this->logger->error($message, (array) $response->getTransferInfo());
				} else {
					$this->logger->info($message);
				}
				break;
			default:
				break;
		}
    }
}
