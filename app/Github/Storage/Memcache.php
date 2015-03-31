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

	function get($key, &$val = null, &$ltl = null, $update = false) {
		if (!$item = $this->mc->get($this->key($key))) {
			return false;
		}

		$val = $item->value;
		$ttl = $item->ttl;
		$set = $item->time;

		if (!isset($ttl)) {
			return true;
		}
		$now = time();
		$ltl = $ttl - ($now - $set);
		if ($ltl >= 0) {
			if ($update) {
				$item->time = time();
				$this->mc->set($this->key($key), $item, $ttl + 60*60*24);
			}
			return true;
		}
		return false;
	}

	function set($key, $val, $ttl = null) {
		$item = new Memcache\Item([
			"value" => $val,
			"ttl" => $ttl,
			"time" => isset($ttl) ? time() : null
		]);
		$this->mc->set($this->key($key), $item, isset($ttl) ? $ttl + 60*60*24 : 0);
		return $this;
	}

	function del($key) {
		$this->mc->delete($this->key($key));
	}
}

namespace app\Github\Storage\Memcache;

class Item
{
	public $value;
	public $time;
	public $ttl;

	function __construct(array $data) {
		foreach ($data as $key => $val) {
			$this->$key = $val;
		}
	}
}

