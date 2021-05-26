<?php

/**
 * Database
 * PHP version 7.4
 *
 * @category Manager
 * @package  Punk\Fake\Router
 * @author   Rafael Pereira <rafaelrufino>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL
 * @link     https://github.com/RafaelPRufino/Router
 */

namespace Punk\Fake;

use \Punk\Fake\Router\Child as child;
use \Punk\Fake\Router\RouteCollection as RouteCollection;

class Router {

    protected Array $configuration;
    protected RouteCollection $routes;

    /**
     * __construct Router
     * @param array $configuration Array de Configuração da Rota
     * @return self
     */
    public function __construct(Array $configuration = []) {
        $this->configuration = $configuration ?? [];
        $this->routes = new RouteCollection;
    }

    /**
     * Configura rota GET da aplicação.
     * @param string $route URI que deve ser processada
     * @param string $name Nome|Descrição da rota
     * @param \Closure $callable Função que deve ser executada ao processar a rota
     * @param \Closure $middleware Função que deve ser executada antes de processar a rota
     * @param \Closure $forwardingAgent Agente de processamento da rota
     * @return Void
     */
    public function get(string $route, string $name, \Closure $callable, $middleware = null, $forwardingAgent = null): void {
        $this->forwarRoute($route, $name, __FUNCTION__, $callable, $middleware, $forwardingAgent);
    }

    /**
     * Configura rota POST da aplicação.
     * @param string $route URI que deve ser processada
     * @param string $name Nome|Descrição da rota
     * @param \Closure $callable Função que deve ser executada ao processar a rota
     * @param \Closure $middleware Função que deve ser executada antes de processar a rota
     * @param \Closure $forwardingAgent Agente de processamento da rota
     * @return Void
     */
    public function post(string $route, string $name, \Closure $callable, $middleware = null, $forwardingAgent = null): void {
        $this->forwarRoute($route, $name, __FUNCTION__, $callable, $middleware, $forwardingAgent);
    }

    /**
     * Configura rota PUT da aplicação.
     * @param string $route URI que deve ser processada
     * @param string $name Nome|Descrição da rota
     * @param \Closure $callable Função que deve ser executada ao processar a rota
     * @param \Closure $middleware Função que deve ser executada antes de processar a rota
     * @param \Closure $forwardingAgent Agente de processamento da rota
     * @return Void
     */
    public function put(string $route, string $name, \Closure $callable, $middleware = null, $forwardingAgent = null): void {
        $this->forwarRoute($route, $name, __FUNCTION__, $callable, $middleware, $forwardingAgent);
    }

    /**
     * Criar nó da rota
     * @param string $uri URI que deve ser processada
     * @param string $name Nome|Descrição da rota
     * @param \Closure $callable Função que deve ser executada ao processar a rota
     * @param \Closure $middleware Função que deve ser executada antes de processar a rota
     * @param \Closure $forwardingAgent Agente de processamento da rota
     * @return Void
     */
    private function forwarRoute(string $uri, string $name, string $method, \Closure $callable, $middleware, $forwardingAgent): void {
        $child = new child(array(strtoupper($method)), $uri);
        $child->setName($name);
        $child->setCallable($callable);
        $child->setMiddleware($middleware);
        $this->routes->add($child, $forwardingAgent);
    }

    /**
     * Executa o processamento das rotas
     * @return self
     */
    public function run(): self {
        call_user_func_array(array($this->routes, 'dispatch'), func_get_args());
        return $this;
    }

}
