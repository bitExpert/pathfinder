<?php

/**
 * This file is part of the Pathfinder package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\Pathfinder;

use Psr\Http\Message\ServerRequestInterface;

/**
 * A more sophisticated implementation of an {@link \bitExpert\Pathfinder\Router\Router}
 * which will map the current request path to a configured target based on some
 * regex magic.
 *
 * @api
 */
class Psr7Router extends AbstractRouter
{
    /**
     * {@inheritdoc}
     */
    public function match(ServerRequestInterface $request)
    {
        $requestPath = $request->getUri()->getPath();

        $this->logger->debug(sprintf('Analysing request path "%s"', $requestPath));

        $candidates = [];

        /** @var array $routeDefinition */
        foreach ($this->routes as $routeDefinition) {
            $route = $routeDefinition['route'];
            $identifier = $this->getRouteIdentifier($route);

            $this->logger->debug(sprintf('Trying to match requested path to route "%s"', $identifier));

            $urlVars = [];

            if (preg_match_all($routeDefinition['pathMatcher'], $requestPath, $urlVars)) {
                $method = strtoupper(trim($request->getMethod()));
                if (!in_array($method, $route->getMethods())) {
                    $candidates[] = [
                        'route' => $route,
                        'failure' => RoutingResult::FAILED_METHOD_NOT_ALLOWED
                    ];
                    continue;
                }

                // remove all elements which should not be set in the request,
                // e.g. the matching url string as well as all numeric items
                $params = $this->mapParams($urlVars);

                if (!$this->matchParams($route, $params)) {
                    $candidates[] = [
                        'route' => $route,
                        'failure' => RoutingResult::FAILED_BAD_REQUEST
                    ];

                    continue;
                }

                $this->logger->debug(
                    sprintf(
                        'Route "%s" matches. Applying its target...',
                        $identifier
                    )
                );

                return RoutingResult::forSuccess($route, $params);
            }
        }

        $this->logger->debug('No matching route found.');

        if (count($candidates)) {
            $candidate = $candidates[0];
            return RoutingResult::forFailure($candidate['failure'], $candidate['route']);
        }

        return RoutingResult::forFailure(RoutingResult::FAILED_NOT_FOUND);
    }

    /**
     * {@inheritdoc}
     */
    public function generateUri($routeIdentifier, array $params = [])
    {
        if (empty($routeIdentifier)) {
            throw new \InvalidArgumentException(
                'Please provide a route identifier, otherwise a link cannot be created!'
            );
        }

        // try to find path for given $target
        $determinedRouteDefinition = null;
        /** @var array $routeDefinition */
        foreach ($this->routes as $routeDefinition) {
            $route = $routeDefinition['route'];

            $identifier = $this->getRouteIdentifier($route);
            if ($routeIdentifier === $identifier) {
                $determinedRouteDefinition = $routeDefinition;
                break;
            }
        }

        // when no path for the given $target can be found,
        // stop processing...
        if (null === $determinedRouteDefinition) {
            throw new \InvalidArgumentException(sprintf('No route found for identifier "%s"', $routeIdentifier));
        }

        $pathMatcher = $determinedRouteDefinition['pathMatcher'];
        $route = $determinedRouteDefinition['route'];

        $detectedParams = [];

        preg_match_all($pathMatcher, $route->getPath(), $detectedParams);

        $this->validateParams($route, $params, array_keys($this->mapParams($detectedParams)));
        $link = $route->getPath();

        foreach ($params as $name => $value) {
            $link = str_replace('[:' . $name . ']', urlencode($value), $link);
        }

        return $link;
    }

    /**
     * Maps resulting params of a regex match to name=>value array
     *
     * @param array $params
     * @return array
     */
    protected function mapParams(array $params)
    {
        unset($params[0]);
        foreach ($params as $name => $value) {
            if (!is_string($name)) {
                unset($params[$name]);
            } else {
                $params[$name] = urldecode($value[0]);
            }
        }

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPathMatcherForRoute(Route $route)
    {
        $pathMatcher = preg_replace('#\[:(.+?)\]#i', '(?P<$1>[^/]+?)/?', $route->getPath());
        return sprintf('#^%s$#i', $pathMatcher);
    }
}
