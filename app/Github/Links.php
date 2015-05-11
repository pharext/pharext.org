<?php

namespace app\Github;

use http\Params;
use http\QueryString;
use http\Url;

class Links
{
	/**
	 * @var \http\Params
	 */
	private $params;
	
	/**
	 * @var array
	 */
	private $relations = [];
	
	function __construct($header_value) {
		$this->params = new Params($header_value, ",", ";", "=",
			Params::PARSE_RFC5988 | Params::PARSE_ESCAPED);
		if ($this->params->params) {
			foreach ($this->params->params as $link => $param) {
				$this->relations[$param["arguments"]["rel"]] = $link;
			}
		}
	}
	
	function getRelations() {
		return $this->relations;
	}
	
	function getNext() {
		if (isset($this->relations["next"])) {
			return $this->relations["next"];
		}
		if (isset($this->relations["last"])) {
			return $this->relations["last"];
		}
		return null;
	}
	
	function getPrev() {
		if (isset($this->relations["prev"])) {
			return $this->relations["prev"];
		}
		if (isset($this->relations["first"])) {
			return $this->relations["first"];
		}
		return null;
	}
	
	function getLast() {
		if (isset($this->relations["last"])) {
			return $this->relations["last"];
		}
		return null;
	}
	
	function getFirst() {
		if (isset($this->relations["first"])) {
			return $this->relations["first"];
		}
		return null;
	}
	
	function getPage($which) {
		if (($link = $this->{"get$which"}())) {
			$url = new Url($link, null, 0);
			$qry = new QueryString($url->query);
			return $qry->getInt("page", 1);
		}
		return 1;
	}
}
