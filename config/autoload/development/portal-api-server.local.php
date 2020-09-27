<?php

declare(strict_types=1);

/**
 * The configuration of the Portal API server itself.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\PortalApi\Server;

use FactorioItemBrowser\PortalApi\Server\Constant\ConfigKey;

return [
    ConfigKey::PROJECT => [
        ConfigKey::PORTAL_API_SERVER => [
            ConfigKey::ALLOWED_ORIGINS => [
                '#^https?://localhost(:\d+)?$#',
            ],
            ConfigKey::SCRIPT_VERSION => 'local',
            ConfigKey::SESSION_COOKIE_NAME => 'FIB',
            ConfigKey::SESSION_COOKIE_DOMAIN => 'localhost',
            ConfigKey::SESSION_COOKIE_PATH => '/',
            ConfigKey::SESSION_COOKIE_LIFETIME => '+30 days',
            ConfigKey::SESSION_COOKIE_SECURE => false,
            ConfigKey::SESSION_LIFETIME => '+30 days',
            ConfigKey::TEMPORARY_SETTING_LIFETIME => '1 hours',
        ],
    ],
];
