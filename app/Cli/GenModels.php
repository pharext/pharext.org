<?php

namespace app\Cli;

use app\Controller;
use app\Model;
use pq\Connection;

class GenModels implements Controller
{
	private $pq;
	
	function __construct(Connection $pq) {
		$this->pq = $pq;
	}
	
	function __invoke(array $args = null) {
		$tables = $this->pq->exec("SELECT tablename FROM pg_tables WHERE schemaname='public'");
		/* @var $tables \pq\Result */
		foreach ($tables->fetchAllCols("tablename") as $table) {
			$this->genModel($table);
		}
	}
	
	function genModel($entity) {
		$title = ucwords($entity);
		$single = substr($title, -3) == "ies" 
			? substr($title, 0, -3)."y" 
			: rtrim($title, "s");
		$this->genTable($entity, $title, $single."Collection");
		$this->genRowset($single."Collection", $single);
		$this->genRow($single);
	}
	
	function genTable($name, $class, $collection) {
		$ns = explode("\\", $class);
		
		if (count($ns) > 1) {
			$class = array_pop($ns);
			$dir = implode("/", $ns);
			$ns = "\\".implode("\\", $ns);
		} else {
			$ns = "";
			$dir = "";
		}
		
		$file = __DIR__."/../Model/$dir/$class.php";
		
		if (!file_exists($file)) {
			file_put_contents($file, <<<EOD
<?php

namespace app\\Model$ns;

use pq\\Gateway\\Table;

class $class extends Table
{
	protected \$name = "$name";
	protected \$rowset = "app\\\\Model$ns\\\\$collection";
}

EOD
			);
		}
	}
	
	function genRowset($class, $row) {
		$ns = explode("\\", $class);
		
		if (count($ns) > 1) {
			$class = array_pop($ns);
			$dir = implode("/", $ns);
			$ns = "\\".implode("\\", $ns);
		} else {
			$ns = "";
			$dir = "";
		}
		
		$file = __DIR__."/../Model/$dir/$class.php";
		
		if (!file_exists($file)) {
			file_put_contents($file, <<<EOD
<?php

namespace app\\Model$ns;

use pq\\Gateway\\Rowset;

class $class extends Rowset
{
	protected \$row = "app\\\\Model$ns\\\\$row";
}

EOD
			);
		}
	}
	function genRow($class) {
		$ns = explode("\\", $class);
		
		if (count($ns) > 1) {
			$class = array_pop($ns);
			$dir = implode("/", $ns);
			$ns = "\\".implode("\\", $ns);
		} else {
			$ns = "";
			$dir = "";
		}
		
		$file = __DIR__."/../Model/$dir/$class.php";
		
		if (!file_exists($file)) {
			file_put_contents($file, <<<EOD
<?php

namespace app\\Model$ns;

use pq\\Gateway\\Row;

class $class extends Row
{
}

EOD
			);
		}
	}
	
}