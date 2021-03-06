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
     * The key main key of the config.
     */
    public const MAIN = 'portal-api-server';

    /**
     * The origins allowed to access the Portal API server.
     */
    public const ALLOWED_ORIGINS = 'allowed-origins';

    /**
     * The key holding the number of recipes to return per result.
     */
    public const NUMBER_OF_RECIPES_PER_RESULT = 'number-of-recipes-per-result';

    /**
     * The key holding the map of the routes to their corresponding requests.
     */
    public const REQUEST_CLASSES_BY_ROUTES = 'request-classes-by-routes';

    /**
     * The key holding the current version of the scripts.
     */
    public const SCRIPT_VERSION = 'script-version';

    /**
     * The key holding the configuration of the serializer.
     */
    public const SERIALIZER = 'serializer';

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

    /**
     * The key holding whether to use a secure cookie.
     */
    public const SESSION_COOKIE_SECURE = 'session-cookie-secure';

    /**
     * The key holding the lifetime of the sessions themselves.
     */
    public const SESSION_LIFETIME = 'session-lifetime';

    /**
     * The key holding the lifetime of the temporary settings.
     */
    public const TEMPORARY_SETTING_LIFETIME = 'temporary-setting-lifetime';
}
