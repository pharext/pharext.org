<?php

namespace app\Cli;

use app\Config;
use app\Controller;

class Ngrok implements Controller
{
	private $config;
	
	function __construct(Config $config) {
		$this->config = $config;
	}
	
	function __invoke(array $args = null) {
		system($this->config->ngrok->command . " ". implode(" ", array_map("escapeshellarg", [
			"http",
			"--subdomain=pharext",
			"--log=stderr",
			"--authtoken",
			$this->config->ngrok->auth->token,
			"--auth",
			$this->config->ngrok->auth->user .":". $this->config->ngrok->auth->pass,
			"80"
		])));
	}
}