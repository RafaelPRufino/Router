<?php

namespace Punk\Fake\Router;

use \Punk\Fake\Server\Environment as Environment;
use \Punk\Fake\Router\Child;

class Processor {

    private Environment $environment;
    private Child $route;

    /**
     * Construct Processor
     * @param Child $child Rota que deverá ser processada
     */
    public function __construct(Child $child) {
        $this->environment = Environment::instance();
        $this->route = $child;
    }

    /**
     * Invoke Route 
     * Invoca a rota e processa seus parâmetros
     */
    public function invoke() {
        $this->dispatch();
    }

    /**
     * Dispatch
     * Faz o processamento da rota
     */
    protected function dispatch() {
        if ($this->route->is()) {
            return $this->dispatchCallback($this->route->getParams());
        }
    }

    /**
     * Dispatch
     * Faz o processamento da rota
     * @param Array $params Parâmetros que devem ser passados para o processamento da rota
     */
    protected function dispatchCallback(Array $params) {
        array_push($params, $this->environment);

        $middleware = call_user_func_array($this->route->getMiddleware(), $params);

        if ((bool) $middleware && is_array($middleware)) {
            $response = call_user_func_array($this->route->getCallable(), $middleware);
        } else {
            $response = call_user_func_array($this->route->getCallable(), $params);
        }
        
        $response();
    }

    /**
     * Route
     * Retorna a rota
     * @return Child
     */
    public function route(): Child {
        return $this->route;
    }

}
