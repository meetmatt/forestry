<?php

namespace Forestry\Tree;

use Forestry\Framework\Application\HttpApplication;
use Forestry\Framework\Config\Config;
use Forestry\Framework\Database\Postgres;
use Forestry\Tree\Controller\Api;
use Forestry\Tree\Controller\UserInterface;
use Forestry\Tree\Model\Tree;

/**
 * Tree HTTP API endpoint application
 */
class Application extends HttpApplication
{
    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        parent::__construct($config);

        $this->registerRoutes();
        $this->registerServices();
    }

    /**
     * Set routes in router
     */
    private function registerRoutes()
    {
        $this->router->get('/', 'ui:index');
        $this->router->post('/schema', 'api:createSchema');
        $this->router->get('/tree/children', 'api:getChildrenTree');
        $this->router->get('/tree/parents', 'api:getParentTree');
        $this->router->get('/tree/contains', 'api:getContainingTree');
        $this->router->get('/tree/search', 'api:findNode');
        $this->router->get('/node', 'api:getNode');
        $this->router->post('/node', 'api:createNode');
        $this->router->post('/node/delete', 'api:deleteNode');
        $this->router->post('/node/update', 'api:updateNode');
    }

    /**
     * Set services in dependency injection container
     */
    private function registerServices()
    {
        $this->container->set('db', function() {
            return Postgres::createFromOptions($this->config->offsetGet('db'));
        });

        $this->container->set('tree', function() {
            return new Tree($this->container->get('db'));
        });

        $this->container->set('api', function () {
            return new Api($this->container->get('tree'));
        });

        $this->container->set('ui', function () {
            return new UserInterface();
        });
    }
}
