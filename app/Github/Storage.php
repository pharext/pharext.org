<?php

namespace app\Github;

interface Storage
{
	function set($key, Storage\Item $item);
	function get($key, Storage\Item &$item = null, $update = false);
	function del($key);
}
