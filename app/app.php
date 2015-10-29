<?php

use Forestry\Framework\Config\ConfigLoader;
use Forestry\Tree\Application;

require __DIR__ . '/bootstrap.php';
$config = ConfigLoader::loadFromFile(__DIR__ . '/config.php');
return new Application($config);
