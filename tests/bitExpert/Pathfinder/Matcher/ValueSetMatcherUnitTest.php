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
 * Unit test for {@link \bitExpert\Pathfinder\Matcher\ValueSetMatcher}.
 *
 * @covers \bitExpert\Pathfinder\Matcher\ValueSetMatcher
 */
class ValueSetMatcherUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ValueSetMatcher
     */
    protected $matcher;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new ValueSetMatcher(array(
            'one',
            2
        ));
    }

    /**
     * @test
     */
    public function matchesValidValues()
    {
        $this->assertTrue($this->matcher->match('one'));
        $this->assertTrue($this->matcher->match(2));
    }

    /**
     * @test
     */
    public function doesNotMatchInvalidValues()
    {
        $this->assertFalse($this->matcher->match(1));
        $this->assertFalse($this->matcher->match('two'));
    }
}
