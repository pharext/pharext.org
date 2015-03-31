<?php

namespace app;

interface Controller
{
	function __invoke(array $args = null);
}
