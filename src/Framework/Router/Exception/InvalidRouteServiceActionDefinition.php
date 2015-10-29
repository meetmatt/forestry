<?php

namespace Forestry\Framework\Router\Exception;

class InvalidRouteServiceActionDefinition extends \Exception
{
    const MESSAGE = "Route service definition for method %s of URI %s must be in format service_id%saction, %s given";

    /**
     * @param string $method
     * @param string $path
     * @param string $delimiter
     * @param string $definition
     * @return InvalidRouteActionDefinition
     */
    public static function create($method, $path, $delimiter, $definition)
    {
        return new self(sprintf(self::MESSAGE, $method, $path, $delimiter, $definition));
    }
}
