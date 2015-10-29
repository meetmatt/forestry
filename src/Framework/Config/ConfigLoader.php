<?php

namespace Forestry\Framework\Config;

use Forestry\Framework\Config\Exception;

class ConfigLoader
{
    /**
     * @param string $path Path to file with config
     * @throws \Exception
     * @return Config
     */
    public static function loadFromFile($path)
    {
        return new Config(self::readConfigFromFile($path));
    }

    /**
     * @param string $path Path to file with config
     * @return array
     * @throws \Exception
     */
    private static function readConfigFromFile($path)
    {
        self::validateFile($path);

        /** @noinspection PhpIncludeInspection */
        $config = include $path;

        if (!is_array($config)) {
            throw Exception\MustReturnArrayException::create($path);
        }

        return $config;
    }

    /**
     * @param string $path
     * @throws \Exception
     */
    private static function validateFile($path)
    {
        if (!file_exists($path)) {
            throw Exception\FileDoesNotExistException::create($path);
        }

        if (!is_readable($path)) {
            throw Exception\FileNotReadableException::create($path);
        }
    }
}
