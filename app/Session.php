<?php

namespace app;

use ArrayAccess;
use http\Env\Response;
use http\Params;

class Session implements ArrayAccess
{
	function __construct(Config $config, Response $response) {
		foreach ($config->session as $key => $val) {
			ini_set("session.$key", $val);
		}
		if (ini_get("session.use_cookies")) {
			$response->addHeader("Vary", "cookie");
		}
		$response->addHeader("Cache-Control",
			new Params([
				"private" => true,
				"must-revalidate" => true,
				"max-age" => ini_get("session.cache_expire") * 60
			])
		);
		session_start();
	}

	function regenerateId() {
		session_regenerate_id();
		return $this;
	}

	function reset() {
		$_SESSION = array();
		session_destroy();
		return $this;
	}

	function __debugInfo() {
		return $_SESSION;
	}
	
	function &__get($p) {
		return $_SESSION[$p];
	}
	function &offsetGet($o) {
		return $_SESSION[$o];
	}
	function __set($p, $v) {
		$_SESSION[$p] = $v;
	}
	function offsetSet($o, $v) {
		$_SESSION[$o] = $v;
	}
	function __isset($p) {
		return isset($_SESSION[$p]);
	}
	function offsetExists($o) {
		return isset($_SESSION[$o]);
	}
	function __unset($p) {
		unset($_SESSION[$p]);
	}
	function offsetUnset($o) {
		unset($_SESSION[$o]);
	}

}
