<?php

/**
 * This file is part of the Pathfinder package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\Pathfinder;

use bitExpert\Pathfinder\Matcher\Matcher;

/**
 * Route Domain object
 */
class Route
{
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
     * @var callable[][]
     */
    protected $matchers;
    /**
     * @var string
     */
    protected $name;

    /**
     * Creates a new {@link \bitExpert\Pathfinder\Route}.
     *
     * @param array|string $methods The HTTP methods the route is active (e.g. GET, POST, PUT, ...)
     * @param string|null $path
     * @param mixed|null $target
     * @param array $matchers
     */
    public function __construct($methods = [], $path = null, $target = null, $matchers = [])
    {
        $this->path = $path;
        $this->target = $target;
        $this->methods = is_array($methods) ? $methods : [$methods];
        $this->methods = array_map('self::normalizeMethod', $this->methods);
        $this->matchers = $matchers;
    }

    /**
     * Creates a new route instance
     *
     * @param array $methods
     * @param string|null $path
     * @param mixed|null $target
     * @param array $matchers
     * @return Route
     */
    public static function create($methods = [], $path = null, $target = null, $matchers = [])
    {
        return new static($methods, $path, $target, $matchers);
    }

    /**
     * Creates a new GET accepting route
     *
     * @param string|null $path
     * @param mixed|null $target
     * @param callable|callable[] The matcher or array of matchers for the param
     * @return Route
     */
    public static function get($path = null, $target = null, $matchers = [])
    {
        return self::create('GET', $path, $target, $matchers);
    }

    /**
     * Creates a new POST accepting route
     *
     * @param string|null $path
     * @param mixed|null $target
     * @param callable|callable[] The matcher or array of matchers for the param
     * @return Route
     */
    public static function post($path = null, $target = null, $matchers = [])
    {
        return self::create('POST', $path, $target, $matchers);
    }

    /**
     * Creates a new PUT accepting route
     *
     * @param string|null $path
     * @param mixed|null $target
     * @param callable|callable[] The matcher or array of matchers for the param
     * @return Route
     */
    public static function put($path = null, $target = null, $matchers = [])
    {
        return self::create('PUT', $path, $target, $matchers);
    }

    /**
     * Creates a new DELETE accepting route
     *
     * @param string|null $path
     * @param mixed|null $target
     * @param callable|callable[] The matcher or array of matchers for the param
     * @return Route
     */
    public static function delete($path = null, $target = null, $matchers = [])
    {
        return self::create('DELETE', $path, $target, $matchers);
    }

    /**
     * Creates a new OPTIONS accepting route
     *
     * @param string|null $path
     * @param mixed|null $target
     * @param callable|callable[] The matcher or array of matchers for the param
     * @return Route
     */
    public static function options($path = null, $target = null, $matchers = [])
    {
        return self::create('OPTIONS', $path, $target, $matchers);
    }

    /**
     * Creates a new PATCH accepting route
     *
     * @param string|null $path
     * @param mixed|null $target
     * @param callable|callable[] The matcher or array of matchers for the param
     * @return Route
     */
    public static function patch($path = null, $target = null, $matchers = [])
    {
        return self::create('PATCH', $path, $target, $matchers);
    }

    /**
     * Sets the method(s) which the route should accept
     *
     * @param array|string $methods The HTTP method(s) the route should handle
     * @return Route
     */
    public function accepting($methods)
    {
        $methods = is_array($methods) ? $methods : [$methods];

        $instance = clone($this);
        $normalizedMethods = array_map('self::normalizeMethod', $methods);
        $instance->methods = array_unique(array_merge($instance->methods, $normalizedMethods));

        return $instance;
    }

    /**
     * Removes given method(s) from the set of methods the route should handle
     *
     * @param array|string $methods The HTTP method(s) the route should no longer handle
     * @return Route
     */
    public function refusing($methods)
    {
        $methods = is_array($methods) ? $methods : [$methods];

        $instance = clone($this);
        $normalizedMethods = array_map('self::normalizeMethod', $methods);

        $instance->methods = array_diff($instance->methods, $normalizedMethods);

        return $instance;
    }

    /**
     * Sets matcher(s) which the given param should match for the route to be active
     *
     * @param string $param The param name to set the matcher(s) for
     * @param callable|callable[] The matcher or array of matchers for the param
     * @return Route
     */
    public function ifMatches($param, $matchers)
    {
        $instance = clone($this);

        if (!isset($instance->matchers[$param])) {
            $instance->matchers[$param] = [];
        }

        $matchers = is_array($matchers) ? $matchers : [$matchers];

        foreach ($matchers as $matcher) {
            if (!is_callable($matcher)) {
                throw new \InvalidArgumentException(sprintf(
                    'Given matcher is not a callable. See %s for signature.',
                    Matcher::class
                ));
            }
        }

        $instance->matchers[$param] = array_merge($instance->matchers[$param], $matchers);

        return $instance;
    }

    /**
     * Returns a route having removed all formerly set matchers for the param with given name
     *
     * @param string $param The name of the param all matchers should be removed for
     * @return Route
     */
    public function whateverMatches($param)
    {
        if (!isset($this->methods[$param])) {
            return $this;
        }

        $instance = clone($this);

        unset($instance->methods[$param]);

        return $instance;
    }

    /**
     * Returns the route with a new source configuration
     *
     * @param string $path The new path
     * @return Route
     */
    public function from($path)
    {
        $instance = clone($this);
        $instance->path = $path;
        return $instance;
    }

    /**
     * Returns the route with a new target
     *
     * @param mixed $target The new target
     * @return Route
     */
    public function to($target)
    {
        $instance = clone($this);
        $instance->target = $target;
        return $instance;
    }

    /**
     * Returns a new instance of the route carrying the given name
     *
     * @param $name
     * @return Route
     */
    public function named($name)
    {
        $instance = clone($this);
        $instance->name = $name;
        return $instance;
    }

    /**
     * Returns a new instance of the route having the name unset
     *
     * @return Route
     */
    public function noName()
    {
        $instance = clone($this);
        $instance->name = null;
        return $instance;
    }

    /**
     * Returns the path of the route.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns the target which is associated with the route.
     *
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Returns the methods accepted by this route
     *
     * @return array|\string[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Returns defined matchers for params of the route
     *
     * @return array
     */
    public function getMatchers()
    {
        return $this->matchers;
    }

    /**
     * Returns the name of the route
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Helper function to normalize HTTP request methods (trimmed to uppercase)
     *
     * @return callable
     */
    protected function normalizeMethod($method)
    {
        return strtoupper(trim($method));
    }
}
