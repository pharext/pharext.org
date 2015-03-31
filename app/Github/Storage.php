<?php

namespace app\Github;

interface Storage
{
	function set($key, $val, $ttl = null);
	function get($key, &$val = null, &$ttl = null, $update = false);
	function del($key);
}
