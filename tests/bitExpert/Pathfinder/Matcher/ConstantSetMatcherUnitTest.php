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
 * Unit test for {@link \bitExpert\Pathfinder\Matcher\ConstantSetMatcher}.
 *
 * @covers \bitExpert\Pathfinder\Matcher\ConstantSetMatcher
 */
class ConstantSetMatcherUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function matchesAgainstValidValuesOfMatchingPattern()
    {
        $matcher = new ConstantSetMatcher(ConstantSetMatcherTestClass::class, 'TEST_CONSTANT_*');
        $this->assertTrue($matcher(1));
        $this->assertTrue($matcher(2));
        $this->assertTrue($matcher(3));

        $matcher = new ConstantSetMatcher(ConstantSetMatcherTestClass::class, '*_CONSTANT_*');
        $this->assertTrue($matcher(1));
        $this->assertTrue($matcher(2));
        $this->assertTrue($matcher(3));

        $this->assertTrue($matcher('one'));
        $this->assertTrue($matcher('two'));
        $this->assertTrue($matcher('three'));
    }

    /**
     * @test
     */
    public function doesNotMatchAgainstInvalidValueIfPatternMatches()
    {
        $matcher = new ConstantSetMatcher(ConstantSetMatcherTestClass::class, 'TEST_CONSTANT_*');

        $this->assertFalse($matcher(4));
        $this->assertFalse($matcher('one'));
        $this->assertFalse($matcher('two'));
        $this->assertFalse($matcher('three'));

        $matcher = new ConstantSetMatcher(ConstantSetMatcherTestClass::class, '*_CONSTANT_*');
        $this->assertFalse($matcher('four'));
    }

    /**
     * @test
     */
    public function doesNotMatchAgainstValidValuesIfPatternDoesNotMatch()
    {
        $matcher = new ConstantSetMatcher(ConstantSetMatcherTestClass::class, '*_CONST_*');

        $this->assertFalse($matcher(1));
        $this->assertFalse($matcher(2));
        $this->assertFalse($matcher(3));
        $this->assertFalse($matcher('one'));
        $this->assertFalse($matcher('two'));
        $this->assertFalse($matcher('three'));
    }
}
