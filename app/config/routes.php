<?php
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/** @var \Cake\Routing\RouteBuilder $routes */
$routes->setRouteClass(DashedRoute::class);

$routes->scope('/', function (RouteBuilder $builder) {
    $builder->connect('/', ['controller' => 'Pages', 'action' => 'home']);
    $builder->connect('/pages/*', ['controller' => 'Pages', 'action' => 'display']);
    $builder->connect('/u/:id', ['controller' => 'Users', 'action' => 'publicProfile'])
        ->setPass(['id'])
        ->setPatterns(['id' => '\\d+']);

    // Add this
    // New route we're adding for our tagged action.
    // The trailing `*` tells CakePHP that this action has
    // passed parameters.
    $builder->scope('/articles', function (RouteBuilder $builder) {
        $builder->connect('/tagged/*', ['controller' => 'Articles', 'action' => 'tags']);
    });

    $builder->fallbacks();
});