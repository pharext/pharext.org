<?php

namespace app\Model;

use pq\Gateway\Table;

class Tokens extends Table
{
	protected $name = "tokens";
	protected $rowset = "app\\Model\\TokenCollection";
}
