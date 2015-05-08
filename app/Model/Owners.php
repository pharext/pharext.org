<?php

namespace app\Model;

use pq\Gateway\Table;

class Owners extends Table
{
	protected $name = "owners";
	protected $rowset = "app\\Model\\OwnerCollection";
}
