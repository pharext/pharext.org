<?php

namespace app;

try {
	$bootstrap = require "../app/bootstrap.php";
	$injector = $bootstrap(["config", "github", "plates", "model", "web"]);
	return $injector->execute(Web::class);
} catch (\Exception $e) {
	$error = $e->getMessage();
	$stack = $e->getTraceAsString();
	@header("X-Exception: ".get_class($e), false, 500);
	Web::cleanBuffers();
}
?>
<html>
	<head>
		<meta charset="utf-8">
		<title>Application Error</title>
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
	</head>
	<body>
		<div class="container">
			<div class="jumbotron">
				<h1>Application Error</h1>
				<h2>Aww, you really gotta do that?!</h2>
				<p class="text-danger">
					<strong><?= htmlspecialchars($error) ?></strong>
				</p>
				<p>
					Sorry, anyway.
				</p>
			</div>
			<?php if (APP_ENVIRONMENT != "production") : ?>
			<pre><?= htmlspecialchars($stack) ?></pre>
			<?php endif; ?>
		</div>
	</body>
</html>
