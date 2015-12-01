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
 * Matcher which matches against a set of constants defined by class and pattern
 */
class ConstantSetMatcher extends ValueSetMatcher
{
    /**
     * Creates a new {@link \bitExpert\Pathfinder\Matcher\ConstantSetMatcher}.
     *
     * @param mixed $classIdentifier Class or object to get the constants from
     * @param string $pattern A simplified expression using * as placeholder
     */
    public function __construct($classIdentifier, $pattern)
    {
        $regex = $this->transformPatternToRegEx($pattern);
        $values = $this->getConstantValues($classIdentifier, $regex);

        parent::__construct($values);
    }

    /**
     * Returns the constant values of the given class when its name matches the given regex
     *
     * @param $classIdentifier
     * @param $pattern
     * @return array
     */
    protected function getConstantValues($classIdentifier, $regex)
    {
        $reflectionClass = new \ReflectionClass($classIdentifier);
        $constants = $reflectionClass->getConstants();
        $names = array_keys($constants);

        // since ARRAY_FILTER_USE_KEY is introduced in PHP 5.6 and we are currently also supporting
        // PHP 5.5, we need to go the clumsy, less elegant way...
        $filter = function ($name) use ($regex) {
            return preg_match($regex, $name);
        };

        $validNames = array_filter($names, $filter);
        $validValues = [];
        foreach ($validNames as $name) {
            $validValues[] = $constants[$name];
        }

        return $validValues;
    }

    /**
     *
     * @param $pattern
     * @return mixed
     */
    protected function transformPatternToRegEx($pattern)
    {
        $pattern = str_replace('*', '.*', $pattern);
        $pattern = str_replace('?', '.', $pattern);
        return '/^' . $pattern . '$/';
    }
}
