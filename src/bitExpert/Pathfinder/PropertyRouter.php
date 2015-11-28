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
 * Implementation of an {@link \bitExpert\Pathfinder\Router} which listens for a
 * defined request variable holding the action token. Standard listener is 'action'.
 *
 * @api
 */
class PropertyRouter implements Router
{
    /**
     * @var string
     */
    protected $baseURL;
    /**
     * @var string
     */
    protected $listener;
    /**
     * @var mixed|null
     */
    protected $defaultTarget;
    /**
     * @var bool
     */
    protected $specialCharEncoding;
    /**
     * @var string
     */
    protected $targetRequestAttribute;

    /**
     * Creates a new {@link \bitExpert\Pathfinder\PropertyRouter}.
     *
     * @param string $baseURL
     */
    public function __construct($baseURL)
    {
        // completes the base url with a / if not set in configuration
        $this->baseURL = rtrim($baseURL, '/') . '/';
        $this->listener = 'action';
        $this->defaultTarget = null;
        $this->targetRequestAttribute = self::DEFAULT_TARGET_REQUEST_ATTRIBUTE;
        $this->specialCharEncoding = false;
    }

    /**
     * Defines whether htmlspecialchars-encoding should be used or not
     * to encode the url which is created with the createLink() method.
     *
     * @param string $listener
     */
    public function setListener($listener)
    {
        $this->listener = $listener;
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
    public function getTargetRequestAttribute()
    {
        return $this->targetRequestAttribute;
    }

    /**
     * Sets whether htmlspecialchars-encoding should be used or not
     *
     * @param bool $specialCharEncoding
     */
    public function setSpecialCharEncoding($specialCharEncoding)
    {
        $this->specialCharEncoding = (bool) $specialCharEncoding;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveTarget(ServerRequestInterface $request)
    {
        $queryParams = $request->getQueryParams();
        $target = isset($queryParams[$this->listener]) ? $queryParams[$this->listener] : null;
        if (null === $target) {
            $target = $this->defaultTarget;
        }

        return $request->withAttribute($this->getTargetRequestAttribute(), $target);
    }

    /**
     * {@inheritDoc}
     * @throws \InvalidArgumentException
     */
    public function createLink($target, array $params = [])
    {
        if (empty($target)) {
            throw new \InvalidArgumentException('Please provide a target, otherwise a link cannot be build!');
        }

        $action = $this->listener . '=' . $target;
        $params = '&' . http_build_query($params, '', '&');
        $params = ($this->specialCharEncoding) ? htmlspecialchars($params) : $params;
        return $this->baseURL . '?' . $action . $params;
    }
}
