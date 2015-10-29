<?php

namespace Forestry\Framework\Http;

use Forestry\Framework\Router\Exception\RouteNotFoundException;
use Forestry\Framework\Router\Router;

/**
 * Class responsible for HTTP negotiation
 */
class Kernel
{
    /** @var Router */
    private $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
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
