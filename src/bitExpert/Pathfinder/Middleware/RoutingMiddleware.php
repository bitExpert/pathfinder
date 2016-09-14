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

namespace bitExpert\Pathfinder\Middleware;

use bitExpert\Pathfinder\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * A routing middleware uses a router to determine a routing result which
 * will be stored inside the routingResultAttribute for further usage.
 */
interface RoutingMiddleware
{
    /**
     * Returns the name of the request attribute the routing result will be
     * stored in.
     *
     * @return string
     */
    public function getRoutingResultAttribute() : string;

    /**
     * Returns the configured router.
     *
     * @return Router
     */
    public function getRouter() : Router;

    /**
     * PSR-7 middleware signature magic method.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) : ResponseInterface;
}
