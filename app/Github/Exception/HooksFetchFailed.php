<?php

namespace app\Github\Exception;

use app\Github\Exception\RequestException;

class HooksFetchFailed extends \Exception implements RequestException
{
	function __construct($message, $code, $previous = null) {
		parent::__construct($message ?: "Hooks fetch request failed", $code, $previous);
	}
}
