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

namespace bitExpert\Pathfinder;

use bitExpert\Slf4PsrLog\LoggerFactory;

abstract class AbstractRouter implements Router
{
    /**
     * @var \Psr\Log\LoggerInterface the logger instance.
     */
    protected $logger;
    /**
     * @var Route[]
     */
    protected $routes;

    /**
     * Creates a new {@link \bitExpert\Pathfinder\AbstractRouter}.
     *
     * @param Route[] routes
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct(array $routes = [])
    {
        $this->routes = [];
        $this->setRoutes($routes);
        $this->logger = LoggerFactory::getLogger(__CLASS__);
    }

    /**
     * Matches given variables against given matchers and returns
     * if all vars pass all matchers.
     *
     * @param Route $route The route to test the values against
     * @param array $params The names variables and values
     * @return bool
     */
    protected function matchParams(Route $route, array $params) : bool
    {
        $matchers = $route->getMatchers();

        foreach ($params as $name => $value) {
            if (!isset($matchers[$name])) {
                continue;
            }

            $valueMatchers = $matchers[$name];
            foreach ($valueMatchers as $matcher) {
                if (!$matcher($value)) {
                    $this->logger->debug(sprintf(
                        'Value "%s" for param "%s" did not match criteria of matcher "%s"',
                        $value,
                        $name,
                        get_class($matcher)
                    ));
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Validates given params against the required ones and
     * checks for matcher violations afterwards.
     *
     * @param Route $route
     * @param array $params
     * @param array $requiredParams
     * @throws \InvalidArgumentException
     */
    protected function validateParams(Route $route, array $params, array $requiredParams)
    {
        $identifier = $this->getRouteIdentifier($route);
        $givenParams = array_keys($params);

        $missingParams = array_diff($requiredParams, $givenParams);

        if (count($missingParams) > 0) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Error while validating params "%s": Required parameters "%s" are missing',
                    $identifier,
                    implode(', ', $missingParams)
                )
            );
        }

        if (!$this->matchParams($route, $params)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Error while validing params for target "%s": Params don\'t fulfill their matcher\'s criteria',
                    $identifier
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addRoute(Route $route)
    {
        // get the specific path matcher for this route
        $pathMatcher = $this->getPathMatcherForRoute($route);

        $this->routes[] = [
            'pathMatcher' => $pathMatcher,
            'route' => $route
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setRoutes(array $routes)
    {
        $this->routes = [];
        foreach ($routes as $route) {
            $this->addRoute($route);
        }
    }

    /**
     * Returns the identifier string for given route.
     *
     * @param Route $route
     * @return string
     */
    protected function getRouteIdentifier(Route $route) : string
    {
        return empty($route->getName()) ? $route->getTarget() : $route->getName();
    }

    /**
     * Returns the internal identifier of the given route
     * used for matching the request.
     *
     * @param Route $route
     * @return mixed
     */
    abstract protected function getPathMatcherForRoute(Route $route);
}
