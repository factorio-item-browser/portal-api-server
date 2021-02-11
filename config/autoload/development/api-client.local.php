<?php

declare(strict_types=1);

/**
 * The configuration of the API client.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\PortalApi\Server;

use FactorioItemBrowser\Api\Client\Constant\ConfigKey;

return [
    ConfigKey::MAIN => [
        ConfigKey::BASE_URI => 'http://api.fib.dev/',
        ConfigKey::API_KEY => 'factorio-item-browser',
        ConfigKey::TIMEOUT => 60,
    ],
];
