<?php

namespace app\Github\Exception;

use app\Github\Exception\RequestException;

class ContentsFetchFailed extends \Exception implements RequestException
{
	function __construct($message, $code, $previous = null) {
		parent::__construct($message ?: "Contents fetch request failed", $code, $previous);
	}
}
