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

use bitExpert\Pathfinder\Middleware\RoutingMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

/**
 * Unit test for {@link \bitExpert\Pathfinder\Middleware\RoutingMiddleware}.
 *
 * @covers \bitExpert\Pathfinder\Middleware\RoutingMiddleware
 */
class RoutingMiddlewareUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServerRequestInterface
     */
    protected $request;
    /**
     * @var ResponseInterface
     */
    protected $response;
    /**
     * @var Router
     */
    protected $router;
    /**
     * @var RoutingMiddleware
     */
    protected $middleware;

    /**
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->request = new ServerRequest();
        $this->response = new Response();

        $this->router = $this->getMock(Router::class);
        $this->middleware = new RoutingMiddleware($this->router, RoutingResult::class);
    }

    /**
     * @test
     */
    public function requestContainsRoutingResultInRoutingResultAttributeAfterRouting()
    {
        $self = $this;
        $routingResult = RoutingResult::forSuccess('testtarget');

        $this->router->expects($this->any())
            ->method('match')
            ->will($this->returnValue($routingResult));

        $next = function ($request, $response) use ($routingResult, $self) {
            $self->assertSame($routingResult, $request->getAttribute(RoutingResult::class));
        };

        $this->middleware->__invoke($this->request, $this->response, $next);
    }
}
