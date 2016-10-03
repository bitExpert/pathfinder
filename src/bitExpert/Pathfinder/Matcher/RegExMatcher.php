<?php

/**
 * This file is part of the Pathfinder package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types = 1);

namespace bitExpert\Pathfinder\Matcher;

/**
 * Matcher which uses regular expression
 */
class RegExMatcher implements Matcher
{
    /**
     * @var string
     */
    protected $regex;

    /**
     * Creates a new {@link \bitExpert\Pathfinder\Matcher\RegexMatcher}.
     *
     * @param string $regex
     */
    public function __construct(string $regex)
    {
        $this->regex = $regex;
    }

    /**
     * @inheritdoc
     */
    public function __invoke($value) : bool
    {
        $value = str_replace('#', '\#', $value);
        return (preg_match(sprintf('#^%s$#', $this->regex), $value) > 0);
    }
}
