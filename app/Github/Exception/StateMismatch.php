<?php

namespace app\Github\Exception;

use app\Github\Exception\StateException;

class StateMismatch extends \Exception implements StateException
{
	function __construct($sessionState, $requestState, $previous = null) {
		parent::__construct("State does not match ('$requestState'!='$sessionState')", 0, $previous);
	}
}
