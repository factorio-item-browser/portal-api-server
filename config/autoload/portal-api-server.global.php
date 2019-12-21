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
//            ConfigKey::CACHE_DIR => '../../data/cache/portal-api-server'
        ]
    ]
];
