<?php

namespace app;

use ArrayAccess;
use merry\Config;

class Session implements ArrayAccess
{
	function __construct(Config $config) {
		foreach ($config->session as $key => $val) {
			ini_set("session.$key", $val);
		}
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
