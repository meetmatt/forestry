<?php

/**
 * PSR-4 project namespace autoloader
 *
 * @param string $class
 * @return void
 */
spl_autoload_register(function ($class) {
    $prefix = 'Forestry\\';
    $baseDir = realpath(__DIR__ . '/../src/');

    // check class prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // skip to next autoloader
        return;
    }

    // class name relative to root namespace
    $relativeClass = substr($class, $len);

    // replace class root namespace with base directory path
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // load file
    if (file_exists($file)) {
        /** @noinspection PhpIncludeInspection */
        require $file;
    } else {
        throw new \Exception(
            sprintf('Unable to find file for class %s in %s', $class, $file)
        );
    }
});
