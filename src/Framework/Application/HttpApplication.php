<?php

namespace Forestry\Framework\Application;

use Forestry\Framework\Config\Config;
use Forestry\Framework\DependencyInjection\ServiceContainer;
use Forestry\Framework\Http\Kernel;
use Forestry\Framework\Http\Request;
use Forestry\Framework\Router\Router;

abstract class HttpApplication
{
    /** @var Config */
    protected $config;

    /** @var ServiceContainer */
    protected $container;

    /** @var Router */
    protected $router;

    /** @var Kernel */
    protected $kernel;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->container = new ServiceContainer();
        $this->router = new Router($this->container);
        $this->kernel = new Kernel($this->router);
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function run(Request $request = null)
    {
        if (!isset($request)) {
            $request = Request::buildFromGlobals();
        }

        $response = $this->kernel->handleRequest($request);
        $this->kernel->sendResponse($response);
    }
}
