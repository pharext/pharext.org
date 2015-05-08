<?php

namespace app\Model;

use pq\Gateway\Table;

class Authorities extends Table
{
	protected $name = "authorities";
	protected $rowset = "app\\Model\\AuthorityCollection";
}
