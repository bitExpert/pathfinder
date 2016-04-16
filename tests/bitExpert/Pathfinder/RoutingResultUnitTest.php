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

/**
 * Unit test for {@link \bitExpert\Pathfinder\RoutingResult}.
 *
 * @covers \bitExpert\Pathfinder\RoutingResult
 */
class RoutingResultUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function forSuccessGeneratesValidResult()
    {
        $params = ['param1' => 'value1', 'param2' => 'value2'];
        $route = Route::get('/test')->to('testAction');
        $result = RoutingResult::forSuccess($route, $params);

        $this->assertTrue($result->succeeded());
        $this->assertFalse($result->failed());
        $this->assertTrue($result->hasRoute());
        $this->assertSame($route, $result->getRoute());

        $this->assertSame($params, $result->getParams());
    }

    /**
     * @test
     */
    public function getParamsReturnsEmptyArrayIfNotBeenSet()
    {
        $route = Route::get('/test')->to('testAction');
        $result = RoutingResult::forSuccess($route);
        $this->assertEquals([], $result->getParams());
    }

    /**
     * @test
     */
    public function forFailureGeneratesValidResultWithoutRoute()
    {
        $result = RoutingResult::forFailure(RoutingResult::FAILED_NOT_FOUND);

        $this->assertTrue($result->failed());
        $this->assertFalse($result->succeeded());
        $this->assertFalse($result->hasRoute());
        $this->assertNull($result->getRoute());
        $this->assertSame([], $result->getParams());
    }
}
