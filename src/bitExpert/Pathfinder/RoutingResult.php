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

/**
 * Class representing the outcome of a routing process
 *
 * @api
 */
class RoutingResult
{
    /**
     * @var bool
     */
    protected $success;
    /**
     * @var mixed
     */
    protected $target;
    /**
     * @var array
     */
    protected $params;

    /**
     * Creates a new {@link bitExpert\Pathfinder\RoutingResult}

     * Constructor is set private to enforce usage of factory methods for success or failure
     */
    private function __construct()
    {
        $this->target = null;
        $this->params = [];
    }

    /**
     * Factory method to create a {@link \bitExpert\Pathfinder\RoutingResult}
     * if the routing process succeeded
     *
     * @param mixed $target
     * @param array $params
     * @return RoutingResult
     */
    public static function forSuccess($target, array $params = [])
    {
        $result = new self();
        $result->success = true;
        $result->target = $target;
        $result->params = $params;

        return $result;
    }

    /**
     * Factory method to create a new {@link bitExpert\Pathfinder\RoutingResult}
     * Target may be set optionally if any fallback / default target needs to be set
     *
     * @param mixed | null $target
     * @return RoutingResult
     */
    public static function forFailure($target = null)
    {
        $result = new self();
        $result->success = false;
        $result->target = $target;

        return $result;
    }

    /**
     * Returns the target determined by the routing process
     *
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Returns the params key/value pairs determined by the routing process
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Returns whether the routing process succeeded
     *
     * @return mixed
     */
    public function succeeded()
    {
        return $this->success;
    }

    /**
     * Returns whether the routing process failed
     *
     * @return bool
     */
    public function failed()
    {
        return !$this->success;
    }

    /**
     * Returns whether the result carries a target
     *
     * @return bool
     */
    public function hasTarget()
    {
        return (null !== $this->target);
    }

    /**
     * Returns whether the result carries a callable target
     *
     * @return bool
     */
    public function hasCallableTarget()
    {
        return $this->hasTarget() && is_callable($this->target);
    }
}
