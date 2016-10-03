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

use Psr\Http\Message\ServerRequestInterface;

/**
 * Router interface
 *
 * @api
 */
interface Router
{
    /**
     * Adds a route to the routes collection.
     *
     * @param Route $route
     * @throws \InvalidArgumentException
     */
    public function addRoute(Route $route);

    /**
     * Sets the routes for the router. Will discard all previous
     * defined routes!
     *
     * @param array $routes
     * @throws \InvalidArgumentException
     */
    public function setRoutes(array $routes);

    /**
     * Resolves the target using the configured routes. Will return null
     * in case no target could be found and no default target was provided.
     *
     * @param ServerRequestInterface $request
     * @return RoutingResult
     */
    public function match(ServerRequestInterface $request) : RoutingResult;

    /**
     * Creates a link to a target identified by the given target identifier. In
     * case building the link is not possible null is returned. Will throw an
     * \InvalidArgumentException when no $targetIdentifier was passed.
     *
     * @param mixed $target
     * @param array $params
     * @return string
     * @throws \InvalidArgumentException
     */
    public function generateUri($target, array $params = []) : string;
}
