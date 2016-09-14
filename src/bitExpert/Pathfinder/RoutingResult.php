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
 * Class representing the outcome of a routing process
 *
 * @api
 */
class RoutingResult
{
    const FAILED_BAD_REQUEST = 400;
    const FAILED_NOT_FOUND = 404;
    const FAILED_METHOD_NOT_ALLOWED = 405;

    /**
     * @var bool
     */
    protected $success;
    /**
     * @var Route
     */
    protected $route;
    /**
     * @var array
     */
    protected $params;
    /**
     * @var int
     */
    protected $failure;

    /**
     * Creates a new {@link bitExpert\Pathfinder\RoutingResult}.
     *
     * Constructor is set private to enforce usage of factory methods for success or failure.
     */
    private function __construct()
    {
        $this->route = null;
        $this->params = [];
    }

    /**
     * Factory method to create a {@link \bitExpert\Pathfinder\RoutingResult}
     * if the routing process succeeded.
     *
     * @param Route $route
     * @param array $params
     * @return RoutingResult
     */
    public static function forSuccess(Route $route, array $params = [])
    {
        $result = new self();
        $result->success = true;
        $result->route = $route;
        $result->params = $params;

        return $result;
    }

    /**
     * Factory method to create a new {@link bitExpert\Pathfinder\RoutingResult}.
     *
     * Target may be set optionally if any fallback / default target needs to be set.
     *
     * @param int $failure
     * @param Route | null $route
     * @return RoutingResult
     */
    public static function forFailure($failure, Route $route = null)
    {
        $result = new self();
        $result->success = false;
        $result->failure = $failure;
        $result->route = $route;

        return $result;
    }

    /**
     * Returns the route determined by the routing process.
     *
     * If success == false, it may carry the first found possible candidate
     * which does not fulfill all criteria to match exactly.
     *
     * @return Route | null
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Returns the failure reason if success == false.
     */
    public function getFailure()
    {
        return $this->failure;
    }

    /**
     * Returns the params key/value pairs determined by the routing process.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Returns whether the routing process succeeded.
     *
     * @return mixed
     */
    public function succeeded()
    {
        return $this->success;
    }

    /**
     * Returns whether the routing process failed.
     *
     * @return bool
     */
    public function failed()
    {
        return !$this->success;
    }

    /**
     * Returns whether the result carries a route.
     *
     * @return bool
     */
    public function hasRoute()
    {
        return (null !== $this->route);
    }
}
