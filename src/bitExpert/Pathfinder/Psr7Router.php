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
 * A more sophisticated implementation of an {@link \bitExpert\Pathfinder\RouterInterface}
 * which will map the current request path to a configured target based on some
 * regex magic.
 *
 * @api
 */
class Psr7Router extends AbstractRouter
{
    /**
     * {@inheritDoc}
     */
    public function match(ServerRequestInterface $request)
    {
        $requestUri = $request->getUri();

        // strip query string if provided
        $requestPath = $requestUri->getPath();
        $queryStringPos = strpos($requestPath, '?');
        if (false !== $queryStringPos) {
            $requestPath = substr($requestPath, 0, $queryStringPos);
        }

        if (!isset($this->routes[$request->getMethod()]) || null === $requestPath) {
            $this->logger->error(
                sprintf(
                    'No routes found for request method "%s". Returning default target "%s"',
                    $request->getMethod(),
                    $this->defaultTarget
                )
            );

            return $request->withAttribute($this->targetRequestAttribute, $this->defaultTarget);
        }

        $this->logger->debug(sprintf('Analysing request path "%s"', $requestPath));

        foreach ($this->routes[$request->getMethod()] as $routeDefinition) {
            $route = $routeDefinition['route'];
            $identifier = $this->getRouteIdentifier($route);

            $this->logger->debug(sprintf('Trying to match requested path to route "%s"', $identifier));

            $urlVars = [];
            if (preg_match_all($routeDefinition['pathMatcher'], $requestPath, $urlVars)) {
                // remove all elements which should not be set in the request,
                // e.g. the matching url string as well as all numeric items
                $params = $this->mapParams($urlVars);

                // match params against configured matchers and only continue if valid
                if ($this->matchParams($route, $params)) {
                    $params = array_merge($request->getQueryParams(), $params);
                    // setting route params as query params
                    $request = $request->withQueryParams($params);
                    $identifier = $this->getRouteIdentifier($route);
                    $this->logger->debug(
                        sprintf(
                            'Route "%s" matches. Applying its target...',
                            $identifier
                        )
                    );

                    return $request->withAttribute($this->targetRequestAttribute, $route->getTarget());
                }
            }
        }

        $this->logger->debug('No matching route found. Applying default target.');

        return $request->withAttribute($this->getTargetRequestAttribute(), $this->defaultTarget);
    }

    /**
     * {@inheritDoc}
     * @throws \InvalidArgumentException
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

        foreach ($this->routes as $routeDefinitions) {
            foreach ($routeDefinitions as $routeDefinition) {
                $route = $routeDefinition['route'];
                $identifier = $this->getRouteIdentifier($route);

                if ($routeIdentifier === $identifier) {
                    $determinedRouteDefinition = $routeDefinition;
                    break 2;
                }
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
     * {@inheritDoc}
     */
    protected function getPathMatcherForRoute(Route $route)
    {
        $pathMatcher = preg_replace('#\[:(.+?)\]#i', '(?P<$1>[^/]+?)/?', $route->getPath());
        return sprintf('#^%s$#i', $pathMatcher);
    }
}
