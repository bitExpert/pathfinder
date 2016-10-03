<?php

/**
 * This file is part of the Pathfinder package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types = 1);

namespace bitExpert\Pathfinder;

/**
 * This class wraps route creation in order to offer a convenient readable way for defining a route
 * without allowing the route to be in an inconsistent state.
 */
class RouteBuilder
{
    /**
     * @var string
     */
    protected static $defaultRouteClass = Route::class;
    /**
     * @var string
     */
    protected $routeClass;
    /**
     * @var string[]
     */
    protected $methods;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var string
     */
    protected $target;
    /**
     * @var callable[]
     */
    protected $matchers;
    /**
     * @var string
     */
    protected $name;


    /**
     * RouteBuilder constructor.
     *
     * @param string $routeClass The route class to build the route from
     * @throws \InvalidArgumentException
     */
    protected function __construct($routeClass)
    {
        if (!is_a($routeClass, Route::class, true)) {
            throw new \InvalidArgumentException(sprintf(
                'The configured route class "%s" is not of or does not inherit class "%s"',
                $routeClass,
                Route::class
            ));
        }

        $this->routeClass = $routeClass;

        $this->path = null;
        $this->target = null;
        $this->name = null;
        $this->methods = [];
        $this->matchers = [];
    }

    /**
     * Sets the route class to build the route from globally.
     *
     * @param string $defaultRouteClass
     */
    public static function setDefaultRouteClass($defaultRouteClass = Route::class)
    {
        self::$defaultRouteClass = $defaultRouteClass;
    }

    /**
     * Creates a new {@link bitExpert\Pathfinder\RouteBuilder} instance using the given
     * $routeClass to create a new route from.
     *
     * @param string|null $routeClass
     * @return RouteBuilder
     */
    public static function route(string $routeClass = null) : RouteBuilder
    {
        $routeClass = $routeClass ? $routeClass : self::$defaultRouteClass;

        return new static($routeClass);
    }

    /**
     * Builds a route using the provided configuration.
     *
     * @return Route
     */
    public function build() : Route
    {
        return new $this->routeClass($this->methods, $this->path, $this->target, $this->matchers, $this->name);
    }

    /**
     * Creates a new HEAD accepting route.
     *
     * @param string $path
     * @return RouteBuilder
     */
    public function head(string $path) : RouteBuilder
    {
        return $this->from($path)->accepting('HEAD');
    }

    /**
     * Creates a new GET accepting route.
     *
     * @param string $path
     * @return RouteBuilder
     */
    public function get(string $path) : RouteBuilder
    {
        return $this->from($path)->accepting('GET');
    }

    /**
     * Creates a new POST accepting route.
     *
     * @param string $path
     * @return RouteBuilder
     */
    public function post(string $path) : RouteBuilder
    {
        return $this->from($path)->accepting('POST');
    }

    /**
     * Creates a new PUT accepting route.
     *
     * @param string $path
     * @return RouteBuilder
     */
    public function put(string $path) : RouteBuilder
    {
        return $this->from($path)->accepting('PUT');
    }

    /**
     * Creates a new DELETE accepting route.
     *
     * @param string $path
     * @return RouteBuilder
     */
    public function delete(string $path) : RouteBuilder
    {
        return $this->from($path)->accepting('DELETE');
    }

    /**
     * Creates a new OPTIONS accepting route.
     *
     * @param string $path
     * @return RouteBuilder
     */
    public function options(string $path) : RouteBuilder
    {
        return $this->from($path)->accepting('OPTIONS');
    }

    /**
     * Creates a new PATCH accepting route.
     *
     * @param string $path
     * @return RouteBuilder
     */
    public function patch(string $path) : RouteBuilder
    {
        return $this->from($path)->accepting('PATCH');
    }

    /**
     * Sets the method(s) which the route should accept.
     *
     * @param string $method The HTTP method(s) the route should handle
     * @return RouteBuilder
     */
    public function accepting(string $method) : RouteBuilder
    {
        $method = strtoupper($method);
        $this->methods = array_unique(array_merge($this->methods, [$method]));

        return $this;
    }

    /**
     * Removes given method(s) from the set of methods the
     * route should handle.
     *
     * @param string $method The HTTP method(s) the route should no longer handle
     * @return RouteBuilder
     */
    public function refusing(string $method) : RouteBuilder
    {
        $method = strtoupper($method);
        $this->methods = array_diff($this->methods, [$method]);

        return $this;
    }

    /**
     * Sets matcher(s) which the given param should match
     * for the route to be active.
     *
     * @param string $param The param name to set the matcher(s) for
     * @param callable $matcher The matcher or array of matchers for the param
     * @return RouteBuilder
     * @throws \InvalidArgumentException
     */
    public function ifMatches(string $param, callable $matcher) : RouteBuilder
    {
        if (!array_key_exists($param, $this->matchers)) {
            $this->matchers[$param] = [];
        }

        $this->matchers[$param][] = $matcher;

        return $this;
    }

    /**
     * Returns a route having removed all formerly set matchers
     * for the param with given name.
     *
     * @param string $param The name of the param all matchers should be removed for
     * @return RouteBuilder
     */
    public function whateverMatches(string $param) : RouteBuilder
    {
        if (array_key_exists($param, $this->matchers)) {
            unset($this->matchers[$param]);
        }

        return $this;
    }

    /**
     * Returns the route with a new source configuration.
     *
     * @param string $path The new path
     * @return RouteBuilder
     */
    public function from(string $path) : RouteBuilder
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Returns the route with a new target.
     *
     * @param mixed $target The new target
     * @return RouteBuilder
     */
    public function to($target) : RouteBuilder
    {
        $this->target = $target;
        return $this;
    }

    /**
     * Returns a new instance of the route carrying the given name.
     *
     * @param string $name
     * @return RouteBuilder
     */
    public function named(string $name) : RouteBuilder
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns a new instance of the route having the name unset.
     *
     * @return RouteBuilder
     */
    public function noName() : RouteBuilder
    {
        $this->name = null;
        return $this;
    }
}
