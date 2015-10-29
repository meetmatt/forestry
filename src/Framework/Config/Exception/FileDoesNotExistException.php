<?php

namespace Forestry\Framework\Config\Exception;

class FileDoesNotExistException extends \Exception
{
    /**
     * @param string $path
     * @return self
     */
    public static function create($path)
    {
        return new self(sprintf('Config file %s does not exist', $path));
    }
}
