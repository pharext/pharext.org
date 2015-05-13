<?php

namespace app\Github\Storage;

use app\Github\Storage;

class Memcache implements Storage
{
	private $mc;
	private $ns;

	function __construct($ns = "github", \Memcached $mc = null) {
		$this->ns = $ns;
		if (!$mc) {
			$mc = new \Memcached("pharext");
			$mc->addServer("localhost", 11211);
		}
		$this->mc = $mc;
	}

	private function key($key) {
		return sprintf("%s:%s", $this->ns, $key);
	}

	function get($key, Item &$item = null, $update = false) {
		if (!$item = $this->mc->get($this->key($key))) {
			return false;
		}

		if (null === $item->getTTL()) {
			return true;
		}
		if ($item->getLTL() >= 0) {
			if ($update) {
				$item->setTimestamp();
				$this->mc->set($this->key($key), $item, $item->getTTL() + 60*60*24);
			}
			return true;
		}
		return false;
	}

	function set($key, Item $item) {
		$this->mc->set($this->key($key), $item, (null !== $item->getTTL()) ? $item->getTTL() + 60*60*24 : 0);
		return $this;
	}

	function del($key) {
		$this->mc->delete($this->key($key));
	}
}
