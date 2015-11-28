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

use bitExpert\Slf4PsrLog\LoggerFactory;
use Psr\Http\Message\ServerRequestInterface;

/**
 * A more sophisticated implementation of an {@link \bitExpert\Pathfinder\Router}
 * which will map the current request path to a configured target based on some
 * regex magic.
 *
 * @api
 */
class RegExRouter implements Router
{
    /**
     * @var \Psr\Log\LoggerInterface the logger instance.
     */
    protected $logger;
    /**
     * @var string
     */
    protected $baseURL;
    /**
     * @var mixed|null
     */
    protected $defaultTarget;
    /**
     * @var array
     */
    protected $routes;
    /**
     * @var string
     */
    protected $targetRequestAttribute;

    /**
     * Creates a new {@link \bitExpert\Pathfinder\RegexRouter}.
     *
     * @param string $baseURL
     */
    public function __construct($baseURL)
    {
        // completes the base url with a / if not set in configuration
        $this->baseURL = rtrim($baseURL, '/') . '/';
        $this->defaultTarget = null;
        $this->targetRequestAttribute = self::DEFAULT_TARGET_REQUEST_ATTRIBUTE;
        $this->routes = [];

        $this->logger = LoggerFactory::getLogger(__CLASS__);
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultTarget($defaultTarget)
    {
        $this->defaultTarget = $defaultTarget;
    }

    /**
     * {@inheritDoc}
     */
    public function setTargetRequestAttribute($targetRequestAttribute)
    {
        $this->targetRequestAttribute = $targetRequestAttribute;
    }

    /**
     * {@inheritDoc}
     */
    public function getTargetRequestAttribute()
    {
        return $this->targetRequestAttribute;
    }

    /**
     * Sets the routes.
     *
     * @param array $routes
     */
    public function setRoutes(array $routes)
    {
        foreach ($routes as $route) {
            if ($route instanceof RegexRouter) {
                //@TODO: Concatenate paths in this case
                $this->routes = array_merge($this->routes, $route->routes);
                continue;
            }

            if ($route instanceof Route) {
                $this->validateRoute($route);

                // convert the given route path into the regex needed
                $path = preg_replace('#\[:(.+?)\]#i', '(?P<$1>[^/]+?)/?', $route->getPath());
                $pathRegex = sprintf('#^%s$#i', $path);

                $methods = $route->getMethods();

                foreach ($methods as $method) {
                    if (!isset($this->routes[$method])) {
                        $this->routes[$method] = [];
                    }

                    $this->routes[$method][] = [
                        'pathRegEx' => $pathRegex,
                        'route' => $route
                    ];
                }
            } else {
                throw new \InvalidArgumentException(sprintf(
                    'Given route is not an instance of %s',
                    Route::class
                ));
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function resolveTarget(ServerRequestInterface $request)
    {
        $requestUri = $request->getUri();
        if (!isset($this->routes[$request->getMethod()]) || null === $requestUri) {
            $this->logger->error(
                sprintf(
                    'No routes found for request method "%s". Returning default target "%s"',
                    $request->getMethod(),
                    $this->defaultTarget
                )
            );

            return $request->withAttribute($this->targetRequestAttribute, $this->defaultTarget);
        }

        // strip query string if provided
        $requestPath = $requestUri->getPath();
        $queryStringPos = strpos($requestPath, '?');
        if (false !== $queryStringPos) {
            $requestPath = substr($requestPath, 0, $queryStringPos);
        }

        $this->logger->debug(sprintf('Analysing request path "%s"', $requestPath));

        foreach ($this->routes[$request->getMethod()] as $routeDefinition) {
            $route = $routeDefinition['route'];
            $this->logger->debug(sprintf('Trying to match requested path to route "%s"', $route->getPath()));

            $urlVars = [];
            if (preg_match_all($routeDefinition['pathRegEx'], $requestPath, $urlVars)) {
                // remove all elements which should not be set in the request,
                // e.g. the matching url string as well as all numeric items
                $params = $this->mapParams($urlVars);

                // match params against configured matchers and only continue if valid
                if ($this->matchParams($route->getMatchers(), $params)) {
                    // setting route params as query params
                    $request = $request->withQueryParams($params);

                    $this->logger->debug(
                        sprintf(
                            'Matching route found. Setting target to "%s"',
                            $route->getTarget()
                        )
                    );

                    return $request->withAttribute($this->targetRequestAttribute, $route->getTarget());
                }
            }
        }

        $this->logger->debug(
            sprintf(
                'No matching route found. Setting default target "%s"',
                $this->defaultTarget
            )
        );

        return $request->withAttribute($this->getTargetRequestAttribute(), $this->defaultTarget);
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
     * Matches given variables against given matchers and returns
     * if all vars pass all matchers
     *
     * @param array $matchers The matchers to test the values against
     * @param array $urlVars The names variables and values
     * @return bool
     */
    protected function matchParams($matchers, $urlVars)
    {
        foreach ($urlVars as $name => $value) {
            if (!isset($matchers[$name])) {
                continue;
            }

            $valueMatchers = $matchers[$name];
            foreach ($valueMatchers as $matcher) {
                if (!$matcher->match($value)) {
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
     * Validates given route for configuration correctness and throws \ConfigurationException
     * if any required configuration is missing. Returns true if everything's fine
     *
     * @param Route $route
     * @throws \ConfigurationException
     * @return boolean
     */
    protected function validateRoute(Route $route)
    {
        if (null === $route->getPath()) {
            throw new \ConfigurationException('Route must have defined a path');
        }

        if (null === $route->getTarget()) {
            throw new \ConfigurationException('Route must have defined a target');
        }

        if (0 === count($route->getMethods())) {
            throw new \ConfigurationException('Route must at least accept one request method');
        }

        return true;
    }

    /**
     * {@inheritDoc}
     * @throws \InvalidArgumentException
     */
    public function createLink($target, array $params = [])
    {
        if (empty($target)) {
            throw new \InvalidArgumentException('Please provide a target identifier, otherwise a link cannot be created!');
        }

        // try to find path for given $target
        $path = '';
        $pathRegEx = '';
        $matchers = [];
        foreach ($this->routes as $routeDefinitions) {
            foreach ($routeDefinitions as $routeDefinition) {
                $pathRegEx = $routeDefinition['pathRegEx'];
                $route = $routeDefinition['route'];

                if ($target === $route->getTarget()) {
                    $path = $route->getPath();
                    $matchers = $route->getMatchers();
                    break 2;
                }
            }
        }

        // when no path for the given $target can be found,
        // stop processing...
        if (empty($path)) {
            throw new \InvalidArgumentException(sprintf('No route found for target "%s"', $target));
        }

        // try to replace all params in the path
        foreach ($params as $name => $value) {
            $applicableMatchers = isset($matchers[$name]) ? $matchers[$name] : [];

            foreach ($applicableMatchers as $applicableMatcher) {
                // if one value does not adhere the matcher's rule stop processing
                if (!$applicableMatcher->match($value)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Could not create link to target "%s": Value "%s" for param "%s" didn\'t match '.
                            'defined matcher of type "%s".',
                            $target,
                            $value,
                            $name,
                            get_class($applicableMatcher)
                        )
                    );
                }
            }

            $path = str_replace('[:' . $name . ']', urlencode($value), $path);
        }

        // here we test for mandatory params which haven't been set
        $params = [];
        preg_match_all($pathRegEx, $path, $params);
        $params = $this->mapParams($params);
        $missingParams = [];

        foreach ($params as $name => $value) {
            if (false !== strpos($value, '[:')) {
                $missingParams[] = $name;
            }
        }

        // in case not all placeholders could be replaced, throw an exception telling which params are missing
        if (count($missingParams) > 0) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Could not create link to target "%s": Undefined value(s) for %s',
                    $target,
                    implode(', ', $missingParams)
                )
            );
        }

        return $path;
    }
}
