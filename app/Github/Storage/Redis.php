<?php

namespace app\Github\Storage;

use app\Github\Storage;

class Redis implements Storage
{
	private $rd;
	private $ns;

	function __construct($ns = "github", \Redis $rd = null) {
		$this->ns = $ns;
		if (!$rd) {
			$rd = new \Redis();
			$rd->open("localhost");
			$rd->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
		}
		$this->rd = $rd;
	}

	private function key($key) {
		return sprintf("%s:%s", $this->ns, $key);
	}

	function get($key, Item &$item = null, $update = false) {
		if (!$item = $this->rd->get($this->key($key))) {
			return false;
		}

		if (null === $item->getTTL()) {
			return true;
		}
		if ($item->getLTL() >= 0) {
			if ($update) {
				$item->setTimestamp();
				$this->rd->setex($this->key($key), $item->getTTL() + 60*60*24, $item);
			}
			return true;
		}
		return false;
	}

	function set($key, Item $item) {
		if (null === $item->getTTL()) {
			$this->rd->set($this->key($key), $item);
		} else {
			$this->rd->setex($this->key($key), $item->getTTL() + 60*60*24, $item);
		}
		return $this;
	}

	function del($key) {
		$this->rd->delete($this->key($key));
	}
}
