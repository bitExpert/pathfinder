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

use bitExpert\Pathfinder\Helper\InheritedRouteHelper;
use bitExpert\Pathfinder\Matcher\Matcher;

/**
 * Unit test for {@link \bitExpert\Pathfinder\RouteBuilder}.
 */
class RouteBuilderUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        RouteBuilder::setDefaultRouteClass();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        RouteBuilder::setDefaultRouteClass();
    }

    /**
     * @test
     */
    public function settingPathReturnsBuilder()
    {
        $builderOriginal = RouteBuilder::route();
        $builder = $builderOriginal->from('/');

        $this->assertSame($builderOriginal, $builder);
    }

    /**
     * @test
     */
    public function settingTargetReturnsBuilder()
    {
        $builderOriginal = RouteBuilder::route();
        $builder = $builderOriginal->to('test');

        $this->assertSame($builderOriginal, $builder);
    }

    /**
     * @test
     */
    public function settingNameReturnsBuilder()
    {
        $builderOriginal = RouteBuilder::route();
        $builder = $builderOriginal->named('testRoute');

        $this->assertSame($builderOriginal, $builder);
    }

    /**
     * @test
     */
    public function unsettingNameReturnsBuilder()
    {
        $builderOriginal = RouteBuilder::route();
        $builder = $builderOriginal->noName();

        $this->assertSame($builderOriginal, $builder);
    }

    /**
     * @test
     */
    public function addingMatcherReturnsBuilder()
    {
        $builderOriginal = RouteBuilder::route();
        $builder = $builderOriginal->ifMatches('id', $this->getMock(Matcher::class));

        $this->assertSame($builderOriginal, $builder);
    }

    /**
     * @test
     */
    public function removingMatcherReturnsBuilder()
    {
        $builderOriginal = RouteBuilder::route();
        $builder = $builderOriginal->whateverMatches('id');

        $this->assertSame($builderOriginal, $builder);
    }

    /**
     * @test
     */
    public function buildReturnsRouteIfConfiguredProperly()
    {
        $route = RouteBuilder::route()
            ->get('/')
            ->to('test')
            ->build();

        $this->assertInstanceOf(Route::class, $route);
    }


    /**
     * @test
     */
    public function setPathIsUsedForRoute()
    {
        $route = RouteBuilder::route()
            ->get('/')
            ->to('test')
            ->build();

        $this->assertEquals('/', $route->getPath());
    }

    /**
     * @test
     */
    public function setTargetIsUsedForRoute()
    {
        $route = RouteBuilder::route()
            ->get('/')
            ->to('test')
            ->build();

        $this->assertEquals('test', $route->getTarget());
    }

    /**
     * @test
     */
    public function setNameIsUsedForRoute()
    {
        $route = RouteBuilder::route()
            ->get('/')
            ->to('test')
            ->named('testRoute')
            ->build();

        $this->assertEquals('testRoute', $route->getName());
    }

    /**
     * @test
     */
    public function setMethodsAreUsedForRoute()
    {
        $route = RouteBuilder::route()
            ->get('/')
            ->to('test')
            ->build();

        $this->assertEquals(['GET'], $route->getMethods());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwsExceptionIfCustomRouteClassDoesNotInheritRoute()
    {
        RouteBuilder::setDefaultRouteClass(\stdClass::class);
        RouteBuilder::route();
    }

    /**
     * @test
     * @dataProvider httpMethodDataprovider
     */
    public function routeCreationShortcutFunctionsCreateCorrectRoutes($method)
    {
        $target = 'test';
        $path = '/[:param]';

        $this->assertRouteCreationShortcutFunction($method, $path, $target);
    }

    /**
     * @test
     */
    public function acceptingAddsAcceptedMethodToRoute()
    {
        $route = RouteBuilder::route()
                ->accepting('get')
                ->accepting('head')
                ->from('/')
                ->to('home')
                ->build();

        $this->assertArraySubset(['GET', 'HEAD'], $route->getMethods());
    }

    /**
     * @test
     */
    public function refusingRemovesAcceptedMethodFromRoute()
    {
        $route = RouteBuilder::route()
            ->accepting('get')
            ->accepting('head')
            ->refusing('head')
            ->from('/')
            ->to('home')
            ->build();

        $this->assertArraySubset(['GET'], $route->getMethods());
    }

    /**
     * @test
     */
    public function ifMatchesAddsMatcherToRoute()
    {
        $route = RouteBuilder::route()
            ->get('/[:test]')
            ->to('test')
            ->ifMatches('test', $this->getMock(Matcher::class))
            ->ifMatches('test', $this->getMock(Matcher::class))
            ->build();

        $matchers = $route->getMatchers();
        $this->assertEquals(1, count($matchers));
        $this->assertArrayHasKey('test', $matchers);
        $this->assertEquals(2, count($matchers['test']));
    }

    /**
     * @test
     */
    public function whateverMatchesRemovesParamMatchersFromRoute()
    {
        $route = RouteBuilder::route()
            ->get('/[:test]')
            ->to('test')
            ->ifMatches('test', $this->getMock(Matcher::class))
            ->ifMatches('test', $this->getMock(Matcher::class))
            ->whateverMatches('test')
            ->build();

        $matchers = $route->getMatchers();
        $this->assertEquals(0, count($matchers));
    }

    /**
     * @test
     */
    public function routeUsesCustomRouteClass()
    {
        $route = RouteBuilder::route(InheritedRouteHelper::class)
            ->get('/')
            ->to('home')
            ->build();

        $this->assertInstanceOf(InheritedRouteHelper::class, $route);
    }

    /**
     * @test
     */
    public function usesGloballySetCustomRouteClass()
    {
        RouteBuilder::setDefaultRouteClass(InheritedRouteHelper::class);

        $route1 = RouteBuilder::route()
            ->get('/')
            ->to('home')
            ->build();

        $route2 = RouteBuilder::route()
            ->get('/test')
            ->to('test')
            ->build();

        $this->assertInstanceOf(InheritedRouteHelper::class, $route1);
        $this->assertInstanceOf(InheritedRouteHelper::class, $route2);
    }

    /**
     * Asserts that the static route creation function for given method works
     *
     * @param $method
     * @param string|null $path
     * @param string|null $target
     * @param array $matchers
     */
    protected function assertRouteCreationShortcutFunction($method, $path = null, $target = null, $matchers = [])
    {
        /** @var Route $route */
        $builder = RouteBuilder::route();
        $builder = call_user_func([$builder, $method], $path);
        $builder->to($target);
        $route = $builder->build();
        $this->assertInstanceOf(Route::class, $route);
        $this->assertTrue(in_array(strtoupper($method), $route->getMethods()));
        $this->assertEquals($path, $route->getPath());
        $this->assertEquals($target, $route->getTarget());
        $this->assertEquals($matchers, $route->getMatchers());
    }

    /**
     * Dataprovider to return the http methods to use in the different testcases.
     *
     * @return array
     */
    public function httpMethodDataprovider()
    {
        return [
            ['head'],
            ['get'],
            ['post'],
            ['put'],
            ['delete'],
            ['options'],
            ['patch']
        ];
    }
}
