<?php

namespace Forestry\Framework\Router\Exception;

class InvalidRouteActionDefinition extends \Exception
{
    const MESSAGE = "Route definition for method %s of URI %s must be of type callable or string, %s given";

    /**
     * @param string $method
     * @param string $path
     * @param mixed $action
     * @return InvalidRouteActionDefinition
     */
    public static function create($method, $path, $action)
    {
        return new self(sprintf(self::MESSAGE, $method, $path, gettype($action)));
    }
}
