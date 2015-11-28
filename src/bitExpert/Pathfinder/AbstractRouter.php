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

abstract class AbstractRouter implements RouterInterface
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
     * Matches given variables against given matchers and returns
     * if all vars pass all matchers
     *
     * @param Route $route The route to test the values against
     * @param array $params The names variables and values
     * @return bool
     */
    protected function matchParams(Route $route, $params)
    {
        $matchers = $route->getMatchers();

        foreach ($params as $name => $value) {
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
     * Validates given params against the required ones and checks for matcher violations afterwards
     *
     * @param Route $route
     * @param array $params
     * @param array $requiredParams
     */
    protected function validateParams(Route $route, array $params, array $requiredParams)
    {
        $target = $route->getTarget();
        $givenParams = array_keys($params);

        $missingParams = array_diff($requiredParams, $givenParams);

        if (count($missingParams) > 0) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Error while validating params "%s": Required parameters "%s" are missing',
                    $target,
                    implode(', ', $missingParams)
                )
            );
        }

        if (!$this->matchParams($route, $params)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Error while validing params for target "%s": Params don\'t fulfill their matcher\'s criteria',
                    $target
                )
            );
        }
    }

    /**
     * Sets the routes.
     *
     * @param array $routes
     */
    public function setRoutes(array $routes)
    {
        foreach ($routes as $route) {
            if ($route instanceof static) {
                //@TODO: Concatenate paths in this case
                $this->routes = array_merge($this->routes, $route->routes);
                continue;
            }

            if ($route instanceof Route) {
                $this->validateRoute($route);
                // convert the given route path into the regex needed
                $identifier = $this->getRouteIdentifier($route);

                $methods = $route->getMethods();

                foreach ($methods as $method) {
                    if (!isset($this->routes[$method])) {
                        $this->routes[$method] = [];
                    }

                    $this->routes[$method][] = [
                        'identifier' => $identifier,
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
     * Validates given route for configuration correctness and throws \ConfigurationException
     * if any required configuration is missing. Returns true if everything's fine
     *
     * @param Route $route
     * @throws \ConfigurationException
     * @return boolean
     */
    protected function validateRoute(Route $route)
    {
        if (null === $route->getSource()) {
            throw new \ConfigurationException('Route must have defined a source');
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
     * Returns the internal identifier of the given route used for matching the request
     *
     * @param Route $route
     * @return mixed
     */
    abstract protected function getRouteIdentifier(Route $route);
}
