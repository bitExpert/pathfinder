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
 * Matcher which matches against a set of given values
 */
class ValueSetMatcher implements Matcher
{
    /**
     * @var array
     */
    protected $validValues;

    /**
     * Creates a new {@link \bitExpert\Pathfinder\Matcher\ValueSetMatcher}.
     *
     * @param array $validValues
     */
    public function __construct(array $validValues)
    {
        $this->validValues = $validValues;
    }

    /**
     * @inheritdoc
     */
    public function __invoke($value)
    {
        return in_array($value, $this->validValues);
    }
}
