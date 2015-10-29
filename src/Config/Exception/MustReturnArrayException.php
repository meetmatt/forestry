<?php

namespace Forestry\Config\Exception;

class MustReturnArrayException extends \Exception
{
    /**
     * @param string $path
     * @return self
     */
    public static function create($path)
    {
        return new self(sprintf('Config file %s must return a PHP array', $path));
    }
}
