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
    ConfigKey::PROJECT => [
        ConfigKey::API_CLIENT => [
            ConfigKey::OPTIONS => [
                ConfigKey::OPTION_API_URL => 'http://api.fib.dev/',
                ConfigKey::OPTION_ACCESS_KEY => 'debug',
                ConfigKey::OPTION_TIMEOUT => 60,
            ],
        ],
    ],
];
