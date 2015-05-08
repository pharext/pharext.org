<?php

namespace app\Github\Exception;

use app\Github\Exception\RequestException;

class ReleaseCreateFailed extends \Exception implements RequestException
{
	function __construct($message, $code, $previous = null)
	{
		parent::__construct($message ?: "Release create request failed", $code, $previous);
	}
}
