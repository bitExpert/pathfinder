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
    protected $attributeName;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->attributeName = RoutingResult::class;
    }

    /**
     * Sets the name of the request attribute the routing result will be stored in
     *
     * @param $attributeName
     */
    public function setAttributeName($attributeName)
    {
        $this->attributeName = $attributeName;
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
