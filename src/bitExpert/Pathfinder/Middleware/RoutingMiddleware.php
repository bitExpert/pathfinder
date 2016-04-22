<?php

/**
 * This file is part of the Pathfinder package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\Pathfinder\Middleware;

use bitExpert\Pathfinder\Router;
use bitExpert\Pathfinder\RoutingResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RoutingMiddleware
{
    /**
     * @var Router
     */
    protected $router;
    /**
     * @var String
     */
    protected $routingResultAttribute;

    /**
     * @param Router $router
     */
    public function __construct(Router $router, $routingResultAttribute)
    {
        $this->router = $router;
        $this->routingResultAttribute = $routingResultAttribute;
    }

    /**
     * Returns the name of the request attribute the routing result will be stored in
     *
     * @return String
     */
    public function getRoutingResultAttribute()
    {
        return $this->routingResultAttribute;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $result = $this->router->match($request);

        if ($next) {
            $response = $next($request->withAttribute($this->attributeName, $result), $response);
        }

        return $response;
    }
}
