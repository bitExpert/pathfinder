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

use bitExpert\Pathfinder\Matcher\Matcher;

/**
 * Unit test for {@link \bitExpert\Pathfinder\Route}.
 *
 * @covers \bitExpert\Pathfinder\Route
 */
class RouteUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function methodSetByConstructorGetsReturnedInCapitalLetters()
    {
        $route = new Route('get');
        $this->assertSame(['GET'], $route->getMethods());

        $route = Route::create('get', '/', 'test');
        $this->assertSame(['GET'], $route->getMethods());
    }

    /**
     * @test
     */
    public function methodSetByFunctionGetsReturnedInCapitalLetters()
    {
        $route = Route::create()->accepting('get');
        $this->assertSame(['GET'], $route->getMethods());
    }

    /**
     * @test
     */
    public function methodSetByFactoryGetsReturnedInCapitalLetters()
    {
        $route = Route::create('get');
        $this->assertSame(['GET'], $route->getMethods());
    }

    /**
     * @test
     */
    public function pathSetByConstructorGetsReturnedAsIs()
    {
        $route = new Route('GET', '/info');
        $this->assertSame('/info', $route->getPath());
    }

    /**
     * @test
     */
    public function pathSetByFunctionGetsReturnedAsIs()
    {
        $route = Route::create()->from('/info');
        $this->assertSame('/info', $route->getPath());
    }

    /**
     * @test
     */
    public function pathSetByFactoryGetsReturnedAsIs()
    {
        $route = Route::create('GET', '/info');
        $this->assertSame('/info', $route->getPath());
    }

    /**
     * @test
     */
    public function targetSetByConstructorGetsReturnedAsIs()
    {
        $route = new Route('get', '/info', 'test');
        $this->assertSame('test', $route->getTarget());
    }

    /**
     * @test
     */
    public function targetTokenSetByFunctionGetsReturnedAsIs()
    {
        $route = Route::create()->to('test');
        $this->assertSame('test', $route->getTarget());
    }

    /**
     * @test
     */
    public function targetSetByFactoryGetsReturnedAsIs()
    {
        $route = Route::create('GET', '/test', 'test');
        $this->assertSame('test', $route->getTarget());
    }

    /**
     * @test
     */
    public function settingPathIsImmutable()
    {
        $route = Route::create();
        $route2 = $route->from('/test');

        $this->assertInstanceOf(Route::class, $route2);
        $this->assertNotEquals($route->getPath(), $route2->getPath());
    }

    /**
     * @test
     */
    public function settingTargetIsImmutable()
    {
        $route = Route::create();
        $route2 = $route->to('test');

        $this->assertNotSame($route, $route2);
    }

    /**
     * @test
     */
    public function addingMethodIsImmutable()
    {
        $route = Route::create();
        $route2 = $route->accepting('GET');

        $this->assertNotSame($route, $route2);
    }

    /**
     * @test
     */
    public function removingMethodIsImmutable()
    {
        $route = Route::create(['POST', 'GET']);
        $route2 = $route->refusing('GET');

        $this->assertNotSame($route, $route2);
    }

    /**
     * @test
     */
    public function addingMatcherIsImmutable()
    {
        $matcher = $this->getMock(Matcher::class);
        $route = Route::create('/user/[:id]');
        $route2 = $route->ifMatches('id', $matcher);

        $this->assertNotSame($route, $route2);
    }

    /**
     * @test
     */
    public function removingMatcherIsImmutable()
    {
        $matcher = $this->getMock(Matcher::class);
        $route = Route::create('/user/[:id]')->ifMatches('id', $matcher);
        $route2 = $route->whateverMatches('id');

        $this->assertNotSame($route, $route2);
    }

    /**
     * @test
     */
    public function callingNamedSetsNameCorrectly()
    {
        $name = 'routeName';
        $route = Route::create()->named($name);

        $this->assertEquals($name, $route->getName());
    }

    /**
     * @test
     */
    public function callingNamedIsImmutable()
    {
        $name = 'routeName';
        $route = Route::create();
        $route2 = $route->named($name);

        $this->assertNotSame($route, $route2);
    }

    /**
     * @test
     */
    public function callingNoNameUnsetsNameCorrectly()
    {
        $name = 'routeName';
        $route = Route::create()->named($name)->noName();

        $this->assertNull($route->getName());
    }

    /**
     * @test
     */
    public function callingNoNameIsImmutable()
    {
        $name = 'routeName';
        $route = Route::create()->named($name);
        $route2 = $route->noName();

        $this->assertNotSame($route, $route2);
    }

    /**
     * @test
     */
    public function acceptsCallableMatchers()
    {
        $thrown = false;

        try {
            Route::get('/order/[:orderId]')
                ->to('my.GetActionTokenWithFunctionMatcher')
                ->ifMatches('orderId', function ($orderId) {
                    return ((int)$orderId > 0);
                });
        } catch (\Exception $e) {
            $thrown = true;
        }

        $this->assertFalse($thrown);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwsExceptionIfMatcherIsNotCallable()
    {
        Route::get('/user/[:userId]')->to('myAction')->ifMatches('userId', 'notCallable');
    }

    /**
     * @test
     */
    public function whateverMatchesRemovesMatcher()
    {
        $route = Route::get('/[:param]')
            ->to('myAction')
            ->ifMatches('id', $this->getMockForAbstractClass(Matcher::class));

        $route = $route->whateverMatches('id');

        $this->assertArrayNotHasKey('id', $route->getMatchers());
    }

    /**
     * @test
     */
    public function returnsTrueIfTargetIsCallable()
    {
        $route = Route::get('/users')->to(function () {
           // do nothing
        });

        $this->assertTrue($route->hasCallableTarget());
    }


    /**
     * @test
     */
    public function staticCreationFunctionsCreateCorrectRoutes()
    {
        $methods = [
            'get',
            'post',
            'put',
            'delete',
            'options',
            'patch'
        ];

        $target = 'test';
        $path = '/[:param]';
        $matchers = [
            'param' => $this->getMockForAbstractClass(Matcher::class)
        ];

        foreach ($methods as $method) {
            $this->assertStaticRouteCreationFunction($method, $path, $target, $matchers);
        }
    }

    /**
     * Asserts that the static route creation function for given method works
     *
     * @param $method
     * @param string|null $path
     * @param string|null $target
     * @param array $matchers
     */
    protected function assertStaticRouteCreationFunction($method, $path = null, $target = null, $matchers = [])
    {
        /** @var Route $route */
        $route = forward_static_call(array(Route::class, $method), $path, $target, $matchers);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertTrue(in_array(strtoupper($method), $route->getMethods()));
        $this->assertEquals($path, $route->getPath());
        $this->assertEquals($target, $route->getTarget());
        $this->assertEquals($matchers, $route->getMatchers());
    }
}
