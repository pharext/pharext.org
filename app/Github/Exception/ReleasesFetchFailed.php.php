<?php

namespace app\Github\Exception;

use app\Github\Exception\RequestException;

class ReleasesFetchFailed extends \Exception implements RequestException
{
	function __construct($message, $code, $previous = null) {
		parent::__construct($message ?: "Releases fetch request failed", $code, $previous);
	}
}
