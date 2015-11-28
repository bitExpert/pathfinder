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
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new NumericMatcher();
    }

    /**
     * @test
     */
    public function matchesNumericValue()
    {
        $result = $this->matcher->match('1234567');
        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function doesNotMatchNumericValuesStartingWithZero()
    {
        $result = $this->matcher->match('01234567');
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function doesNotMatchWhenCharactersAreIncluded()
    {
        $result = $this->matcher->match('1234a567');
        $this->assertFalse($result);
    }
}
