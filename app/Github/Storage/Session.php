<?php

namespace app\Github\Storage;

use app\Github\Storage;

class Session implements Storage
{
	private $ns;

	function __construct($ns = "github") {
		$this->ns = $ns;
	}

	function set($key, Item $item) {
		$_SESSION[$this->ns][$key] = $item;
		return $this;
	}

	function get($key, Item &$item = null, $update = false) {
		if (!isset($_SESSION[$this->ns][$key])) {
			return false;
		}
		$item = $_SESSION[$this->ns][$key];
		if (null === $item->getTTL()) {
			return true;
		}
		if ($item->getLTL() >= 0) {
			if ($update) {
				$item->setTimestamp();
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
