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
 * Route representation class
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
     * @var callable[]
     */
    protected $matchers;
    /**
     * @var string
     */
    protected $name;

    /**
     * Creates a new {@link \bitExpert\Pathfinder\Route}.
     *
     * @param array $methods The HTTP methods the route is active (e.g. GET, POST, PUT, ...)
     * @param string $path
     * @param mixed $target
     * @param array $matchers An array of matchers for params
     * @param string|null $name The name of the route (has to be set if target is not a string)
     */
    public function __construct(array $methods, $path, $target, array $matchers = [], $name = null)
    {
        if (!is_string($path) || empty($path)) {
            throw new \InvalidArgumentException('A route needs a non-empty string as path');
        }

        if (empty($target) || (!is_string($target) && !is_callable($target))) {
            throw new \InvalidArgumentException('A route needs a non-empty string or callable as target');
        }

        if (!empty($name) && !is_string($name)) {
            throw new \InvalidArgumentException('A route name needs to be a string if defined');
        }

        if (count($methods) < 1) {
            throw new \InvalidArgumentException('A route needs to accept at least one method');
        }

        if (!is_string($target) && empty($name)) {
            throw new \InvalidArgumentException('A route with non-string target needs to have a name defined');
        }

        $this->path = $path;
        $this->target = $target;
        $this->name = $name;
        $this->methods = $methods;
        $this->methods = array_map('self::normalizeMethod', $this->methods);
        $this->matchers = $matchers;
    }

    /**
     * Returns the path of the route.
     *
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * Returns the target which is associated with the route.
     *
     * @return string
     */
    public function getTarget() : string
    {
        return $this->target;
    }

    /**
     * Returns the methods accepted by this route.
     *
     * @return array
     */
    public function getMethods() : array
    {
        return $this->methods;
    }

    /**
     * Returns defined matchers for params of the route.
     *
     * @return array
     */
    public function getMatchers() : array
    {
        return $this->matchers;
    }

    /**
     * Returns the name of the route.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Helper function to normalize HTTP request methods
     * (trimmed to uppercase).
     *
     * @return string
     */
    protected function normalizeMethod($method) : string
    {
        return strtoupper(trim($method));
    }
}
