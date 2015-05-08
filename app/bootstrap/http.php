<?php

namespace app;

use http\Env\Request;
use http\Env\Response;

$injector->share(Request::class);
$injector->share(Response::class);
