<?php

namespace app\Github\Storage;

use app\Github\Storage;

class Session implements Storage
{
	private $ns;

	function __construct($ns = "github") {
		$this->ns = $ns;
	}

	function set($key, $val, $ttl = null) {
		$_SESSION[$this->ns][$key] = [$val, $ttl, isset($ttl) ? time() : null];
		return $this;
	}

	function get($key, &$val = null, &$ltl = null, $update = false) {
		if (!isset($_SESSION[$this->ns][$key])) {
			return false;
		}
		list($val, $ttl, $set) = $_SESSION[$this->ns][$key];
		if (!isset($ttl)) {
			return true;
		}
		$now = time();
		$ltl = $ttl - ($now - $set);
		if ($ltl >= 0) {
			if ($update) {
				$_SESSION[$this->ns][$key][2] = $now;
			}
			return true;
		}
		return false;
	}

	function del($key) {
		unset($_SESSION[$this->ns][$key]);
		return $this;
	}
}
