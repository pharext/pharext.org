<?php

namespace app\Github\Exception;

use app\Github\Exception;
use http\Header;
use http\Client\Response;

class RequestException extends \Exception implements Exception
{
	protected $errors = [];
	
	function __construct(Response $response) {
		if (($h = $response->getHeader("Content-Type", Header::class)) 
		&&	$h->match("application/json", Header::MATCH_WORD)
		&&	$failure = json_decode($response->getBody())) {
			$message = $failure->message;
			if (isset($failure->errors)) {
				$this->errors = (array) $failure->errors;
			}
		} else {
			$message = $response->getBody()->toString();
		}
		
		if (!strlen($message)) {
			$message = $response->getTransferInfo("error");
		}
		
		parent::__construct($message, $response->getResponseCode(), null);
	}
	
	function getErrors() {
		return $this->errors;
	}
	
	function getErrorsAsString() {
		static $reasons = [
			"missing" => "The resource %1\$s does not exist\n",
			"missing_field" => "Missing field %2\$s of resource %1\$s\n",
			"invalid" => "Invalid formatting of field %2\$s of resource %1\$s\n",
			"already_exists" => "A resource %1\$s with the same value of field %2\$s already exists\n",
		];
		
		if (!$this->errors) {
			return "";
		}
		
		$errors = "JSON errors:\n";
		foreach ($this->errors as $error) {
			$errors .= sprintf($reasons[$error->code], $error->resource, $error->field);
		}
		return $errors;
	}
	function __toString() {
		return parent::__toString() . "\n". $this->getErrorsAsString();
	}
}
