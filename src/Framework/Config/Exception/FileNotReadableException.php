<?php

namespace Forestry\Framework\Config\Exception;

class FileNotReadableException extends \Exception
{
    /**
     * @param string $path
     * @return self
     */
    public static function create($path)
    {
        return new self(sprintf('Config file %s is not readable', $path));
    }
}
