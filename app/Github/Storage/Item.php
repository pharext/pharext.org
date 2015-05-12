<?php

namespace app\Github\Storage;

class Item
{
	private $value;
	private $time;
	private $ttl;
	
	function __construct($value, $ttl = null, $time = null) {
		$this->value = $value;
		$this->ttl = $ttl;
		$this->time = $time ?: time();
	}
	
	static function __set_state(array $state) {
		return new static(
			isset($state["value"]) ? $state["value"] : null,
			isset($state["ttl"]) ? $state["ttl"] : null,
			isset($state["time"]) ? $state["time"] : null
		);
	}
	
	function toArray() {
		return get_object_vars($this);
	}
	
	function getTimestamp() {
		return $this->time;
	}
	
	function setTimestamp($ts = null) {
		$this->time = $ts ?: time();
		return $this;
	}
	
	function getTTL() {
		return $this->ttl;
	}
	
	function setTTL($ttl = null) {
		$this->ttl = $ttl;
		return $this;
	}
	
	function getAge($from = null) {
		return ($from ?: time()) - $this->time;
	}
	
	function getLTL($from = null) {
		return $this->ttl - $this->getAge($from);
	}
	
	function getValue() {
		return $this->value;
	}
	
	function setValue($value = null) {
		$this->value = $value;
		return $this;
	}
}
