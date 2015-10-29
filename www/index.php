<?php

define('START', microtime(true));

/** @var Forestry\Tree\Application $app */
$app = require __DIR__ . '/../app/app.php';
$app->run();
