<?php

namespace Forestry\Router;

use Forestry\DependencyInjection\ServiceContainer;
use Forestry\Http\Request;
use Forestry\ParameterBag\ImmutableParameterBag;
use Forestry\Router\Exception\InvalidRouteActionDefinition;
use Forestry\Router\Exception\RouteNotFoundException;

/**
 * Basic route resolver
 */
class Router
{
    const SERVICE_METHOD_DELIMITER = ':';

    /** @var ServiceContainer */
    private $container;

    /** @var ImmutableParameterBag */
    private $routes;

    /**
     * @param ServiceContainer $container
     */
    public function __construct(ServiceContainer $container = null)
    {
        if (isset($container)) {
            $this->container = $container;
        }

        $this->routes = new ImmutableParameterBag();
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
     * @throws RouteNotFoundException
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
        list($serviceId, $method) = $this->parseRoute($action);

        // get service from container
        $service = $this->container->get($serviceId);

        // return callable
        return [$service, $method];
    }

    /**
     * @param string $method
     * @param string $path
     * @param callable|string $action
     * @throws InvalidRouteActionDefinition
     */
    private function add($method, $path, $action)
    {
        if (!is_callable($action) || !is_string($action)) {
            throw InvalidRouteActionDefinition::create($method, $path, $action);
        }

        $this->routes[self::buildRouteKey($method, $path)] = $action;
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
