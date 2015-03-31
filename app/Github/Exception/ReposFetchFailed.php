<?php

namespace app\Github\Exception;

use app\Github\Exception\RequestException;

class ReposFetchFailed extends \Exception implements RequestException
{
	function __construct($message, $code, $previous = null) {
		parent::__construct($message ?: "Repos fetch request failed", $code, $previous);
	}
}
