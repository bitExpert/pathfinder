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
        $route = new Route(['get'], '/', 'test');

        $this->assertSame(['GET'], $route->getMethods());
    }

    /**
     * @test
     */
    public function pathSetByConstructorGetsReturnedAsIs()
    {
        $route = new Route(['get'], '/info', 'test');
        $this->assertSame('/info', $route->getPath());
    }

    /**
     * @test
     */
    public function targetSetByConstructorGetsReturnedAsIs()
    {
        $route = new Route(['get'], '/', 'test');
        $this->assertSame('test', $route->getTarget());
    }

    /**
     * @test
     */
    public function nameSetByConstructorGetsReturnedAsIs()
    {
        $route = new Route(['get'], '/', 'test', [], 'testRoute');
        $this->assertSame('testRoute', $route->getName());
    }

    /**
     * @test
     */
    public function matchersSetByConstructorGetsReturnedAsIs()
    {
        $matchers = [
            'param1' => [
                $this->getMock(Matcher::class),
                $this->getMock(Matcher::class)
            ],
            'param2' => [
                $this->getMock(Matcher::class)
            ]
        ];

        $route = new Route(['get'], '/[:param1]/[:param2]', 'test', $matchers);
        $this->assertSame($matchers, $route->getMatchers());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwsExceptionIfPathIsEmpty()
    {
        new Route(['get'], '', 'testAction');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwsExceptionIfPathIsNotAString()
    {
        new Route(['get'], 123, 'testAction');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwsExceptionIfTargetIsEmpty()
    {
        new Route(['get'], '/test', '');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwsExceptionIfTargetIsNeitherAStringNorACallable()
    {
        new Route(['get'], '/test', 123);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwsExceptionIfNoMethodIsAccepted()
    {
        new Route([], '/test', 'testAction');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwsExceptionIfTargetIsNotAStringAndNoNameIsDefined()
    {
        $target = function () {

        };

        new Route(['get'], '/test', $target);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwsExceptionIfNameIsNotAString()
    {
        new Route([], '/test', 'testAction', [], 123);
    }
}
