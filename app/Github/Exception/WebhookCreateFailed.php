<?php

namespace app\Github\Exception;

class WebhookCreateFailed extends \Exception implements \app\Github\Exception\RequestException
{
	function __construct($message, $code, $previous = null) {
		parent::__construct($message ?: "Webhook create request failed", $code, $previous);
	}
}
