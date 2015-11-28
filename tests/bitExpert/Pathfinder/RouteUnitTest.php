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

        $this->assertInstanceOf(Route::class, $route2);
        $this->assertNotEquals($route->getTarget(), $route2->getTarget());
    }

    /**
     * @test
     */
    public function addingMethodIsImmutable()
    {
        $route = Route::create();
        $route2 = $route->accepting('GET');

        $this->assertInstanceOf(Route::class, $route2);
        $this->assertNotEquals($route->getMethods(), $route2->getMethods());
    }

    /**
     * @test
     */
    public function removingMethodIsImmutable()
    {
        $route = Route::create(['POST', 'GET']);
        $route2 = $route->refusing('GET');

        $this->assertInstanceOf(Route::class, $route2);
        $this->assertNotEquals($route->getMethods(), $route2->getMethods());
    }

    /**
     * @test
     */
    public function addingMatcherIsImmutable()
    {
        $matcher = $this->getMock(Matcher::class);
        $route = Route::create('/user/[:id]');
        $route2 = $route->ifMatches('id', $matcher);

        $this->assertInstanceOf(Route::class, $route2);
        $this->assertNotEquals($route->getMatchers(), $route2->getMatchers());
    }
}
