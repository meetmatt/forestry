<?php

namespace Forestry\Router\Exception;

class RouteNotFoundException extends \Exception
{
    /**
     * @param string $method
     * @param string $path
     * @return RouteNotFoundException
     */
    public static function create($method, $path)
    {
        return new self(sprintf("Route to path %s for method %s not found", $method, $path));
    }
}
