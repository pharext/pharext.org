<?php

namespace app\Github\Exeption;

class UserFetchFailed extends \Exception implements app\Github\Exception\RequestException
{
	function __construct($message, $code, $previous = null) {
		parent::__construct($message ?: "User fetch request failed", $code, $previous);
	}
}
