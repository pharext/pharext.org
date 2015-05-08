<?php

namespace app;

$bootstrap = require "../app/bootstrap.php";
$injector = $bootstrap(["config", "github", "plates", "model", "web"]);
$injector->execute(Web::class);
