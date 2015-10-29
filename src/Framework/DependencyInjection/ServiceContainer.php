<?php

namespace Forestry\Framework\DependencyInjection;

use Forestry\Framework\DependencyInjection\Exception\ServiceNotFoundException;
use Forestry\Framework\ParameterBag\ParameterBag;

class ServiceContainer extends ParameterBag
{
    /**
     * Store service instantiation callable
     *
     * @param string $serviceId
     * @param callable $callable
     */
    public function set($serviceId, callable $callable)
    {
        $this->offsetSet($serviceId, $callable);
    }

    /**
     * Lazy load and cache service by ID
     *
     * @param string $serviceId
     * @return mixed
     * @throws ServiceNotFoundException
     */
    public function get($serviceId)
    {
        if (!$this->offsetExists($serviceId)) {
            throw ServiceNotFoundException::create($serviceId);
        }

        $service = $this->offsetGet($serviceId);
        if (is_callable($service)) {
            $service = call_user_func($service);
            $this->offsetSet($serviceId, $service);
        }

        return $service;
    }
}
