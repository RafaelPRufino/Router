<?php

namespace Punk\Fake\Router;

use \Punk\Fake\Server\Environment as Environment;

class Child {

    private Environment $environment;
    private Array $arguments = array();

    public function __construct($methods, $uri) {
        $this->applyMethods($methods);
        $this->applyUri($uri);
        $this->setParams(array());
        $this->environment = Environment::instance();
    }

    protected function applyMethods($methods) {
        $this->methods = ((bool) $methods) ? $methods : array('GET');
    }

    /**
     * Get route methods
     * @return mixed
     */
    public function getMethods() {
        return $this->methods;
    }

    /**
     * Set route methods
     * @param  mixed $methods
     */
    public function setMethods() {
        $this->applyMethods(func_get_args());
    }

    public function appendMethods() {
        $this->methods = array_merge($this->methods, $args);
    }

    /**
     * Detect support for an HTTP method
     * @return bool
     */
    public function supportsHttpMethod($method) {
        return in_array($method, $this->methods);
    }

    protected function applyUri($uri) {
        $uri = trim($uri);
        if (trim(substr($uri, -1)) === '/' && strlen($uri) > 1) {
            $uri = trim(substr($uri, 0, -1));
        }        
        $pattern = (bool) $uri ? $uri : '/';
        $this->uri = $pattern;
        $this->applyArguments($uri);
    }

    /**
     * Get route uri
     * @return mixed
     */
    public function getUri() {
        return $this->uri;
    }

    /**
     * Set route uri
     * @param  mixed $uri
     */
    public function setUri($uri) {
        $this->applyUri($uri);
    }

    /**
     * Apply default route arguments for all instances
     * @param  array $arguments
     */
    protected function applyArguments($arguments) {
        $matches = is_array($arguments) ? $arguments : explode('/', $arguments);
        $argumentsmatches = array();

        if (is_array($arguments)) {
            $this->arguments = $arguments;
        } else {
            $this->arguments = explode('/', $arguments);
        }

        foreach ($matches as $argument) {
            $patternAsRegex = "/::?([\w]+)/";
            if (preg_match($patternAsRegex, $argument)) {
                array_push($argumentsmatches, str_replace('::', ':', $argument));
            } else {
                array_push($argumentsmatches, $argument);
            }
        }

        $this->arguments = $argumentsmatches;
    }

    /**
     * Get route arguments
     * @return mixed
     */
    public function getArguments() {
        return $this->arguments;
    }

    /**
     * Set route uri
     * @param  mixed $arguments
     */
    public function setArguments($arguments) {
        $this->applyArguments($arguments);
    }

    protected function sentenceCase($string) {
        $sentences = preg_split('/([.?!]+)/', $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $new_string = '';
        foreach ($sentences as $key => $sentence) {
            $new_string .= ($key & 1) == 0 ?
                    ucfirst(strtolower(trim($sentence))) :
                    $sentence . ' ';
        }
        return trim($new_string);
    }

    protected function applyCallable($callable) {
        if (!is_callable($callable)) {
            $callable = function () {
                
            };
        }

        $this->callable = $callable;
    }

    /**
     * Get route callable
     * @return mixed
     */
    public function getCallable() {
        return $this->callable;
    }

    /**
     * Set route callable
     * @param  mixed $callable
     * @throws \InvalidArgumentException If argument is not callable
     */
    public function setCallable($callable) {
        $this->applyCallable($callable);
    }

    protected function applyMiddleware($middleware) {
        if (!is_callable($middleware)) {
            $middleware = function () {
                return func_get_args();
            };
        }

        $this->middleware = $middleware;
    }

    /**
     * Get route callable
     * @return mixed
     */
    public function getMiddleware() {
        return $this->middleware;
    }

    /**
     * Set route callable
     * @param  mixed $middleware
     * @throws \InvalidArgumentException If argument is not callable
     */
    public function setMiddleware($middleware) {
        $this->applyMiddleware($middleware);
    }

    /**
     * Get route name
     * @return string|null
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set route name
     * @param  string $name
     */
    public function setName($name) {
        $this->name = (string) $name;
    }

    /**
     * Get route parameters
     * @return array
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Set route parameters
     * @param  array $params
     */
    public function setParams($params) {
        $this->params = $params;
    }

    /**
     * Set route name
     * @param  string $name The name of the route
     * @return \Punk\Fake\Router\Child self
     */
    public function name($name) {
        $this->setName($name);
        return $this;
    }

    public function is() {
        return $this->matches();
    }

    public function urlFor() {
        return str_replace('/', '\/', implode("/", $this->getArguments()));
    }

    protected function matches() {
        $resourceUri = $this->environment->getResourceUri();
        $resourceMethod = $this->environment->getMethod();
        $routeUri = $this->urlFor();

        if (!$this->supportsHttpMethod($resourceMethod)) {
            return false;
        }

        $this->paramNames = array();
        $this->paramNamesPath = array();

        $patternAsRegex = preg_replace_callback('#:([\w]+)\+?#', array($this, 'matchesCallback'), str_replace(')', ')?', (string) $routeUri));
        if (substr($routeUri, -1) === '/') {
            $patternAsRegex .= '?';
        }

        if (!preg_match('#^' . $patternAsRegex . '$#', $resourceUri, $paramValues)) {
            return false;
        }

        foreach ($this->paramNames as $name) {
            if (isset($paramValues[$name])) {
                if (isset($this->paramNamesPath[$name])) {
                    $this->params[$name] = explode('/', urldecode($paramValues[$name]));
                } else {
                    $this->params[$name] = urldecode($paramValues[$name]);
                }
            }
        }

        return true;
    }

    protected function matchesCallback($m) {
        $this->paramNames[] = $m[1];
        if (isset($this->conditions[$m[1]])) {
            return '(?P<' . $m[1] . '>' . $this->conditions[$m[1]] . ')';
        }
        if (substr($m[0], -1) === '+') {
            $this->paramNamesPath[$m[1]] = 1;
            return '(?P<' . $m[1] . '>.+)';
        }

        return '(?P<' . $m[1] . '>[^/]+)';
    }
}
