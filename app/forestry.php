<?php

namespace Forestry;

require __DIR__ . '/bootstrap.php';

$config = Config\ConfigBuilder::buildFromFile(__DIR__ . '/config.php');
$container = new DependencyInjection\ServiceContainer();

$container->set('api.controller', function(){
   return function(Http\Request $request){
       return 'Test';
   };
});

$router = new Router\Router($container);
$router->get('/', 'api.controller');

return new Application($config, $router);
