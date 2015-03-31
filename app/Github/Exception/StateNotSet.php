<?php

namespace app\Github\Exception;

use app\Github\Exception\StateException;

class StateNotSet extends \Exception implements StateException
{
	function __construct($previous = null) {
		parent::__construct("State is not set", 0, $previous);
	}
}
