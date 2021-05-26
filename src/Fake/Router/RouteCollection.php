<?php

namespace Punk\Fake\Router;

use \Punk\Fake\Router\Child;
use \Punk\Fake\Router\Processor;
use \Punk\Fake\Server\Environment as Environment;

class RouteCollection {

    protected Array $routes = array();
    protected Array $dispatchers = array();

    public function __construct() {
        $this->setRoutes(array());
        $this->setDispatchers(array());
    }

    /**
     * Criar nó da rota
     * @param \Punk\Fake\Router\Child $route nó de rota
     * @param \Closure $forwardingAgent Agente de processamento da rota
     * @return Void
     */
    public function add(Child $route, \Closure $forwardingAgent = null) {
        array_push($this->routes, $route);
        if (!is_callable($forwardingAgent)) {
            $forwardingAgent = function (Processor $route, Environment $server) {
                $route->invoke();
            };
        }
        array_push($this->dispatchers, $forwardingAgent);
    }

    /**
     * Get route arguments
     * @return mixed
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * Set route uri
     * @param  mixed $routes     
     */
    protected function setRoutes($routes) {
        $this->routes = $routes;
    }

    /**
     * Get route dispatchers
     * @return mixed
     */
    public function getDispatchers() {
        return $this->dispatchers;
    }

    /**
     * Set route uri
     * @param  mixed $dispatchers     
     */
    protected function setDispatchers($dispatchers) {
        $this->dispatchers = $dispatchers;
    }

    /**
     * Get route by index
     * @param  int $index route index 
     * @return mixed Rota
     */
    public function getRoute(Integer $index) {

        if ($this->hasIndexRoute($index)) {
            return $this->routes[(int) $index];
        }

        return null;
    }

    /**
     * Has Index Route
     * @param  int $index route index 
     * @return bool indica se rota existe
     */
    public function hasIndexRoute(Integer $index) {
        return isset($this->routes[(int) $index]);
    }

    public function dispatch() {
        $routes = array();
        foreach ($this->getRoutes() as $index => $route) {
            $invoker = new Processor($route);
            if ($invoker->route()->is()) {
                array_push($routes, array('index' => $index, 'route' => $invoker));
                break;
            }
        }

        if (count($routes) > 0) {
            foreach ($routes as $value) {
                $environment = Environment::instance();
                $route = $value['route'];
                $index = $value['index'];
                $forwardingAgent = $this->dispatchers[$index];
                call_user_func_array($forwardingAgent, array($route, $environment));
            }
        }
    }
}
