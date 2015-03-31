<?php

namespace app\Github\Exception;

use app\Github\Exception\TokenException;

class TokenNotSet extends \Exception implements TokenException
{
	function __construct($previous = null) {
		parent::__construct("Token is not set", 0, $previous);
	}
}
