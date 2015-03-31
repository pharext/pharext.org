<?php

namespace app\Github\Exception;

use app\Github\Exception\TokenException;
use app\Github\Exception\RequestException;

class TokenFetchFailed extends \Exception implements TokenException, RequestException
{
	function __construct($message, $code, $previous = null) {
		parent::__construct($message ?: "Token fetch request failed", $code, $previous);
	}
}
