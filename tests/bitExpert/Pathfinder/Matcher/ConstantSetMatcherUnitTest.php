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
        $this->assertTrue($matcher->match(1));
        $this->assertTrue($matcher->match(2));
        $this->assertTrue($matcher->match(3));

        $matcher = new ConstantSetMatcher(ConstantSetMatcherTestClass::class, '*_CONSTANT_*');
        $this->assertTrue($matcher->match(1));
        $this->assertTrue($matcher->match(2));
        $this->assertTrue($matcher->match(3));

        $this->assertTrue($matcher->match('one'));
        $this->assertTrue($matcher->match('two'));
        $this->assertTrue($matcher->match('three'));
    }

    /**
     * @test
     */
    public function doesNotMatchAgainstInvalidValueIfPatternMatches()
    {
        $matcher = new ConstantSetMatcher(ConstantSetMatcherTestClass::class, 'TEST_CONSTANT_*');

        $this->assertFalse($matcher->match(4));
        $this->assertFalse($matcher->match('one'));
        $this->assertFalse($matcher->match('two'));
        $this->assertFalse($matcher->match('three'));

        $matcher = new ConstantSetMatcher(ConstantSetMatcherTestClass::class, '*_CONSTANT_*');
        $this->assertFalse($matcher->match('four'));
    }

    /**
     * @test
     */
    public function doesNotMatchAgainstValidValuesIfPatternDoesNotMatch()
    {
        $matcher = new ConstantSetMatcher(ConstantSetMatcherTestClass::class, '*_CONST_*');

        $this->assertFalse($matcher->match(1));
        $this->assertFalse($matcher->match(2));
        $this->assertFalse($matcher->match(3));
        $this->assertFalse($matcher->match('one'));
        $this->assertFalse($matcher->match('two'));
        $this->assertFalse($matcher->match('three'));
    }
}
