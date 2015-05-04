<?php

namespace app\Github\Exception;

class WebhookDeleteFailed extends \Exception implements \app\Github\Exception\RequestException
{
	function __construct($message, $code, $previous = null) {
		parent::__construct($message ?: "Webhook delete request failed", $code, $previous);
	}
}
