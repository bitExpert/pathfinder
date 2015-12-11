<?php

/**
 * This file is part of the Pathfinder package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\Pathfinder\Matcher;

/**
 * Unit test for {@link \bitExpert\Pathfinder\Matcher\NumericMatcher}.
 *
 * @covers \bitExpert\Pathfinder\Matcher\NumericMatcher
 */
class NumericMatcherUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NumericMatcher
     */
    protected $matcher;

    /**
     * @test
     */
    public function matchesNumericValue()
    {
        $matcher = new NumericMatcher();
        $result = $matcher('1234567');
        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function doesNotMatchNumericValuesStartingWithZero()
    {
        $matcher = new NumericMatcher();
        $result = $matcher('01234567');
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function doesNotMatchWhenCharactersAreIncluded()
    {
        $matcher = new NumericMatcher();
        $result = $matcher('1234a567');
        $this->assertFalse($result);
    }
}
