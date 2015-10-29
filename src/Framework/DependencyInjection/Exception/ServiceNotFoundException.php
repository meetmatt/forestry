<?php

namespace Forestry\Framework\DependencyInjection\Exception;

class ServiceNotFoundException extends \Exception
{
    /**
     * @param string $serviceId
     * @return self
     */
    public static function create($serviceId)
    {
        return new self(sprintf("Service with ID %s not found", $serviceId));
    }

}
