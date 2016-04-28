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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use bitExpert\Pathfinder\Route;
use bitExpert\Pathfinder\Router;
use bitExpert\Pathfinder\RoutingResult;

/**
 * Unit test for {@link \bitExpert\Pathfinder\Middleware\BasicRoutingMiddleware}.
 *
 * @covers \bitExpert\Pathfinder\Middleware\BasicRoutingMiddleware
 */
class BasicRoutingMiddlewareUnitTest extends \PHPUnit_Framework_TestCase
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

        $this->router = $this->getMockForAbstractClass(Router::class);
        $this->middleware = new BasicRoutingMiddleware($this->router, RoutingResult::class);
    }

    /**
     * @test
     */
    public function requestContainsRoutingResultInRoutingResultAttributeAfterRouting()
    {
        $self = $this;
        $route = Route::get('/test')->to('testAction');
        $routingResult = RoutingResult::forSuccess($route);

        $this->router->expects($this->any())
            ->method('match')
            ->will($this->returnValue($routingResult));

        $next = function ($request, $response) use ($routingResult, $self) {
            $self->assertSame($routingResult, $request->getAttribute(RoutingResult::class));
        };

        $this->middleware->__invoke($this->request, $this->response, $next);
    }

    /**
     * @test
     */
    public function returnsRouterCorrectly()
    {
        $this->assertSame($this->router, $this->middleware->getRouter());
    }

    /**
     * @test
     */
    public function returnsRoutingResultAttributeCorrectly()
    {
        $this->assertEquals(RoutingResult::class, $this->middleware->getRoutingResultAttribute());
    }
}
