<?php

namespace app\Github\Exception;

use app\Github\Exception\TokenException;

class TokenExpired extends \Exception implements TokenException
{
	private $seconds;

	function __construct($seconds, $previous = null) {
		$this->seconds = abs($seconds);
		parent::__construct("Token expired $this->seconds seconds ago", 0, $previous);
	}

	function getSeconds() {
		return $this->seconds;
	}
}
