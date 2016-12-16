<?php

if (!function_exists("uri_template")) {
	function uri_template($str, $arr) {
		$tpl = new Rize\UriTemplate;
		return $tpl->expand($str, $arr);
	}
}
