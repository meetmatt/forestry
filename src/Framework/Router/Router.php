<?php

namespace Forestry\Framework\Router;

use Forestry\Framework\DependencyInjection\ServiceContainer;
use Forestry\Framework\Http\Request;
use Forestry\Framework\ParameterBag\ParameterBag;
use Forestry\Framework\Router\Exception\InvalidRouteActionDefinition;
use Forestry\Framework\Router\Exception\InvalidRouteServiceActionDefinition;
use Forestry\Framework\Router\Exception\RouteNotFoundException;

/**
 * Basic route resolver
 */
class Router
{
    const SERVICE_METHOD_DELIMITER = ':';

    /** @var ServiceContainer */
    private $container;

    /** @var ParameterBag */
    private $routes;

    /**
     * @param ServiceContainer $container
     */
    public function __construct(ServiceContainer $container = null)
    {
        if (isset($container)) {
            $this->container = $container;
        }

        $this->routes = new ParameterBag();
    }

    /**
     * @param string $path
     * @param callable|string $action
     */
    public function get($path, $action)
    {
        $this->add(Request::HTTP_GET, $path, $action);
    }

    /**
     * @param string $path
     * @param callable|string $action
     */
    public function post($path, $action)
    {
        $this->add(Request::HTTP_POST, $path, $action);
    }

    /**
     * @param Request $request
     * @return callable
     * @throws InvalidRouteActionDefinition
     * @throws RouteNotFoundException|InvalidRouteServiceActionDefinition
     */
    public function getCallableFromRequest(Request $request)
    {
        $method = $request->getMethod();
        $path = $request->getPath();
        $routeKey = self::buildRouteKey($method, $path);

        // 404
        if (!$this->routes->offsetExists($routeKey)) {
            throw RouteNotFoundException::create($method, $path);
        }

        // get route action
        $action = $this->routes->offsetGet($routeKey);

        // clojure - just return it
        if (is_callable($action)) {
            return $action;
        }

        // parse route action definition
        $serviceFunction = $this->parseRoute($action);
        if (count($serviceFunction) < 2) {
            // no function - callable class or clojure?
            $serviceId = $serviceFunction[0];
            $service = $this->container->get($serviceId);
            return $service;

        } else {
            // split service and function
            list($serviceId, $function) = $serviceFunction;
            $service = $this->container->get($serviceId);
            return [$service, $function];
        }
    }

    /**
     * @param string $method
     * @param string $path
     * @param callable|string $action
     * @throws InvalidRouteActionDefinition
     */
    private function add($method, $path, $action)
    {
        if (!is_callable($action) && !is_string($action)) {
            throw InvalidRouteActionDefinition::create($method, $path, $action);
        }

        $this->routes->offsetSet(self::buildRouteKey($method, $path), $action);
    }

    /**
     * @param string $method
     * @param string $path
     * @return string
     */
    private static function buildRouteKey($method, $path)
    {
        return $method . $path;
    }

    /**
     * @param string $route
     * @return array
     */
    private function parseRoute($route)
    {
        return explode(self::SERVICE_METHOD_DELIMITER, $route, 2);
    }

}
