<?php

namespace app\Github\Fetch;

use app\Github\Exception\TokenFetchFailed;
use app\Github\Fetch;

use http\Client\Request;
use http\QueryString;

class Token extends Fetch
{
	function getRequest() {
		$request = new Request("POST", "https://github.com/login/oauth/access_token", [
			"Accept" => "application/json",
		]);
		$request->getBody()->append(
			new QueryString([
				"client_id" => $this->args["id"],
				"client_secret" => $this->args["secret"],
				"code" => $this->args["code"]
			])
		);
		return $request;
	}

	function getException($message, $code, $previous = null) {
		return new TokenFetchFailed($message, $code, $previous);
	}

	function getCacheKey() {
		return "access_token";
	}

	function getStorage(&$ttl = null) {
		/* do not cache externally */
		return null;
	}
}