<?php

namespace app;

$bootstrap = require "../app/bootstrap.php";
$injector = $bootstrap(["config", "github", "plates", "web"]);
$injector->execute(Web::class);
