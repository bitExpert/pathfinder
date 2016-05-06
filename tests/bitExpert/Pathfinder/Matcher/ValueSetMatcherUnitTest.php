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
 */
class ValueSetMatcherUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ValueSetMatcher
     */
    protected $matcher;

    /**
     * @test
     */
    public function matchesValidValues()
    {
        $matcher = $this->getMatcher();
        $this->assertTrue($matcher('one'));
        $this->assertTrue($matcher(2));
    }

    /**
     * @test
     */
    public function doesNotMatchInvalidValues()
    {
        $matcher = $this->getMatcher();
        $this->assertFalse($matcher(1));
        $this->assertFalse($matcher('two'));
    }

    protected function getMatcher()
    {
        return new ValueSetMatcher(array(
            'one',
            2
        ));
    }
}
