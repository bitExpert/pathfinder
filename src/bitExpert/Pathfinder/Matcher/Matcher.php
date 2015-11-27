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
 * Matcher interface
 *
 * @api
 */
interface Matcher
{
    /**
     * Function to test the given value against implemented criteria
     *
     * @param $param
     * @param $value
     * @return mixed
     */
    public function match($value);
}
