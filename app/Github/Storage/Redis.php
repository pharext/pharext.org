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

	function get($key, &$val = null, &$ltl = null, $update = false) {
		if (!$item = $this->rd->get($this->key($key))) {
			header("Cache-Item: ".serialize($item), false);
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
		header("X-Cache-Times: ltl=$ltl,now=$now,set=$set,ttl=$ttl", false);
		if ($ltl >= 0) {
			if ($update) {
				$item->time = time();
				$this->rd->setex($this->key($key), $ttl + 60*60*24, $item);
			}
			return true;
		}
		return false;
	}

	function set($key, $val, $ttl = null) {
		$item = new Redis\Item([
			"value" => $val,
			"ttl" => $ttl,
			"time" => isset($ttl) ? time() : null
		]);
		if (isset($ttl)) {
			$this->rd->set($this->key($key), $item);
		} else {
			$this->rd->setex($this->key($key), $ttl + 60*60*24, $item);
		}
		return $this;
	}

	function del($key) {
		$this->rd->delete($this->key($key));
	}
}

namespace app\Github\Storage\Redis;

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

