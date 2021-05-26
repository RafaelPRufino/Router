<?php

require __DIR__ . '/vendor/autoload.php';

use Punk\Fake\Router;
use \Punk\Fake\Server\Environment;

// instance application
$app = new Router();

$forwardingAgent = function (Router\Processor $route, Environment $env) {
    if ($env->headers('token-auth') != null) {
        $route->invoke();
    }
};

$middleware = function () {
    $params = func_get_args();
    return $params;
};

$app->get('/lead/:id', 'lead-get', function (int $id, Environment $env) {
    //Display lead by id
}, $middleware, $forwardingAgent);

$app->post('/lead', 'lead-create', function (Environment $env) {
    $name = $env->params('name');
    $lastname = $env->params('lastname');
    $phone = $env->params('phone');

    //create new lead
}, $middleware, $forwardingAgent);

$app->put('/lead/:id', 'lead-update', function (int $id, Environment $env) {
    $params = $env->getBodyJson();
    $name = $params['name'];
    $lastname = $params['lastname'];
    $phone = $params['phone'];
    //update lead by id 
}, $middleware, $forwardingAgent);

$app->get('/authenticate', 'authenticate', function (Environment $env) {
    //get all parameters
    $params = $env->params();
});

$app->run();
