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
class RoutinResultUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function forSuccessGeneratesValidResult()
    {
        $params = ['param1' => 'value1', 'param2' => 'value2'];

        $result = RoutingResult::forSuccess('mySucceededTarget', $params);
        $this->assertTrue($result->succeeded());
        $this->assertFalse($result->failed());
        $this->assertTrue($result->hasTarget());
        $this->assertEquals('mySucceededTarget', $result->getTarget());

        $this->assertSame($params, $result->getParams());
    }

    /**
     * @test
     */
    public function getParamsReturnsEmptyArrayIfNotBeenSet()
    {
        $result = RoutingResult::forSuccess('myTarget');
        $this->assertEquals([], $result->getParams());
    }

    /**
     * @test
     */
    public function forFailureGeneratesValidResultWithoutTarget()
    {
        $result = RoutingResult::forFailure();

        $this->assertTrue($result->failed());
        $this->assertFalse($result->succeeded());
        $this->assertFalse($result->hasTarget());
        $this->assertNull($result->getTarget());
        $this->assertSame([], $result->getParams());
    }

    /**
     * @test
     */
    public function forFailureGeneratesValidResultWithTarget()
    {
        $result = RoutingResult::forFailure('myFallbackTarget');

        $this->assertTrue($result->failed());
        $this->assertFalse($result->succeeded());
        $this->assertTrue($result->hasTarget());
        $this->assertEquals('myFallbackTarget', $result->getTarget());
        $this->assertSame([], $result->getParams());
    }
}
