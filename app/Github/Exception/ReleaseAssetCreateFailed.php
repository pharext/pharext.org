<?php

namespace app\Github\Exception;

use app\Github\Exception\RequestException;

class ReleaseAssetCreateFailed extends \Exception implements RequestException
{
	function __construct($message, $code, $previous = null)
	{
		parent::__construct($message ?: "ReleaseAsset create request failed", $code, $previous);
	}
}
