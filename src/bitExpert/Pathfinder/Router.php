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
 * Router interface
 *
 * @api
 */
interface Router
{
    const DEFAULT_TARGET_REQUEST_ATTRIBUTE = 'Pathfinder.target';

    /**
     * Returns the currently configured name of the attribute to store the target inside the request
     *
     * @return string
     */
    public function getTargetRequestAttribute();

    /**
     * Sets the default target. It is used to retrieve a target, if
     * no route can be resolved to a target.
     *
     * @param mixed $defaultTarget
     */
    public function setDefaultTarget($defaultTarget);

    /**
     * Resolves the target using the configured routes. Will return null
     * in case no target could be found and no default target
     * was provided.
     *
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    public function resolveTarget(ServerRequestInterface $request);

    /**
     * Creates a link to a target identified by the given target identifier. In
     * case building the link is not possible null is returned. Will throw an
     * \InvalidArgumentException when no $targetIdentifier was passed.
     *
     * @param mixed $targetIdentifier
     * @param array $params
     * @return string|null
     * @throws \InvalidArgumentException
     */
    public function createLink($targetIdentifier, array $params = []);
}
