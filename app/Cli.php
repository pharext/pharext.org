<?php

namespace app;

use pharext\Cli\Args;

class Cli
{
	/**
	 * @var \app\Config
	 */
	private $config;
	
	/**
	 * @var \pharext\Cli\Args
	 */
	private $args;
	
	function __construct(Config $config, Args $args) {
		$this->config = $config;
		$this->args = $args;
	}
	
	function __invoke($argc, array $argv, callable $exec) {
		$prog = array_shift($argv);
		foreach ($this->args->parse(--$argc, $argv) as $error) {
			$errs[] = $error;
		}
		
		if ($this->args["help"] || !array_filter($this->args->toArray())) {
			$this->help($prog);
			exit;
		}
		if (!empty($errs)) {
			foreach ($errs as $err) {
				fprintf(STDERR, "ERROR: %s\n", $err);
				exit(-1);
			}
		}
		
		if ($this->args["ngrok"]) {
			$exec(Cli\Ngrok::class);
		}
		if ($this->args["initdb"]) {
			$exec(Cli\Initdb::class);
		}
		if ($this->args["gen-models"]) {
			$exec(Cli\GenModels::class);
		}
	}
	
	function getConfig() {
		return $this->config;
	}
	
	/**
	 * Output command line help message
	 * @param string $prog
	 */
	public function help($prog) {
		printf("Usage:\n\n  \$ %s", $prog);
		
		$flags = [];
		$required = [];
		$optional = [];
		foreach ($this->args->getSpec() as $spec) {
			if ($spec[3] & Args::REQARG) {
				if ($spec[3] & Args::REQUIRED) {
					$required[] = $spec;
				} else {
					$optional[] = $spec;
				}
			} else {
				$flags[] = $spec;
			}
		}
	
		if ($flags) {
			printf(" [-%s]", implode("", array_column($flags, 0)));
		}
		foreach ($required as $req) {
			printf(" -%s <arg>", $req[0]);
		}
		if ($optional) {
			printf(" [-%s <arg>]", implode("|-", array_column($optional, 0)));
		}
		printf("\n\n");
		$spc = $this->args->getSpec();
		$max = $spc ? max(array_map("strlen", array_column($spc, 1))) : 0;
		$max += $max % 8 + 2;
		foreach ($spc as $spec) {
			if (isset($spec[0])) {
				printf("    -%s|", $spec[0]);
			} else {
				printf("    ");
			}
			printf("--%s ", $spec[1]);
			if ($spec[3] & Args::REQARG) {
				printf("<arg>  ");
			} elseif ($spec[3] & Args::OPTARG) {
				printf("[<arg>]");
			} else {
				printf("       ");
			}
			printf("%s%s", str_repeat(" ", $max-strlen($spec[1])+3*!isset($spec[0])), $spec[2]);
			if ($spec[3] & Args::REQUIRED) {
				printf(" (REQUIRED)");
			}
			if (isset($spec[4])) {
				printf(" [%s]", $spec[4]);
			}
			printf("\n");
		}
		printf("\n");
	}
}
