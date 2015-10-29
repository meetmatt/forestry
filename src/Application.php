<?php

namespace Forestry;

use Forestry\Config\Config;
use Forestry\Http\Request;
use Forestry\Http\Response;
use Forestry\Router\Exception\RouteNotFoundException;
use Forestry\Router\Router;

/**
 * God class which is responsible
 *
 * @package Forestry
 */
class Application
{
    /** @var Config */
    private $config;

    /** @var Router */
    private $router;

    /**
     * @param Config $config
     * @param Router $router
     */
    public function __construct(Config $config, Router $router)
    {
        $this->config = $config;
        $this->router = $router;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function handleRequest(Request $request)
    {
        try {
            $callable = $this->router->getCallableFromRequest($request);
            $response = call_user_func($callable, $request);

        } catch (RouteNotFoundException $e) {
            $response = new Response($e->getMessage(), Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            throw $e;
        }

        if (!($response instanceof Response)) {
            if (is_scalar($response)) {
                $response = new Response($response);
            } else {
                $response = new Response((string)$response);
            }
        }

        return $response;
    }

    /**
     * @param Response $response
     */
    public function sendResponse(Response $response)
    {
        $response->send();

        // terminate fastcgi request
        if (preg_match('|fpm|', PHP_SAPI) !== false) {
            fastcgi_finish_request();
        }
    }
}
