<?php

namespace app\Github\Exception;

use app\Github\Exception\StateException;

class StateExpired extends \Exception implements StateException
{
	private $seconds;

	function __construct($seconds, $previous = null) {
		$this->seconds = abs($seconds);
		parent::__construct("State expired $this->seconds seconds ago", 0, $previous);
	}

	function getSeconds() {
		return $this->seconds;
	}
}
