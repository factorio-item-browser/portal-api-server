<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Constant;

/**
 * The interface holding the config keys.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface ConfigKey
{
    /**
     * The key holding the name of the project.
     */
    public const PROJECT = 'factorio-item-browser';

    /**
     * The key holding the name of the API server itself.
     */
    public const PORTAL_API_SERVER = 'portal-api-server';

    /**
     * The origins allowed to access the Portal API server.
     */
    public const ALLOWED_ORIGINS = 'allowed-origins';

    /**
     * The key holding the cache directory to use.
     */
    public const CACHE_DIR = 'cache-dir';

    /**
     * The key holding the number of recipes to return per result.
     */
    public const NUMBER_OF_RECIPES_PER_RESULT = 'number-of-recipes-per-result';

    /**
     * The key holding the name of the session cookie.
     */
    public const SESSION_COOKIE_NAME = 'session-cookie-name';

    /**
     * The key holding the domain to use for the session cookie.
     */
    public const SESSION_COOKIE_DOMAIN = 'session-cookie-domain';

    /**
     * The key holding the path for the session cookie.
     */
    public const SESSION_COOKIE_PATH = 'session-cookie-path';

    /**
     * The key holding the lifetime for the session cookie.
     */
    public const SESSION_COOKIE_LIFETIME = 'session-cookie-lifetime';
}
