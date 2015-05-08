<?php

namespace app;

use merry\Config as Container;

class Config extends Container
{
	function addConfig(Config $config) {
		foreach ($config as $sub => $conf) {
			/* one level down should suffice, i.e. $config->github->client = {id,secret,scope} */
			if ($conf instanceof Config) {
				foreach ($conf as $key => $val) {
					$this->$sub->$key = $val;
				}
			} else {
				$this->$sub = $conf;
			}
		}
	}
}
