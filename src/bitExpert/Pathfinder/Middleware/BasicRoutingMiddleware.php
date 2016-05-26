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

class BasicRoutingMiddleware implements RoutingMiddleware
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
     * Creates a new {@link \bitExpert\Pathfinder\Middleware\BasicRoutingMiddleware}.
     *
     * @param Router $router
     * @param String $routingResultAttribute
     */
    public function __construct(Router $router, $routingResultAttribute)
    {
        $this->router = $router;
        $this->routingResultAttribute = $routingResultAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutingResultAttribute()
    {
        return $this->routingResultAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouter()
    {
        return $this->router;
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
        $request = $this->applyRoutingResult($request, $result);

        if ($next) {
            $response = $next($request->withAttribute($this->routingResultAttribute, $result), $response);
        }

        return $response;
    }

    /**
     * Offers possibility to manipulate the request according to routing result.
     * Returns a new {@link \Psr\Http\Message\ServerRequestInterface}.
     *
     * @param ServerRequestInterface $request
     * @param RoutingResult $routingResult
     * @return ServerRequestInterface
     */
    protected function applyRoutingResult(ServerRequestInterface $request, RoutingResult $routingResult)
    {
        $routingParams = $routingResult->getParams();
        $params = array_merge($request->getQueryParams(), $routingParams);
        return $request->withQueryParams($params);
    }
}
