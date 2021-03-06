<?php

/**
 * The configuration of the Laminas log.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server;

use Laminas\Log\Logger;
use Laminas\Log\LoggerInterface;
use Laminas\Log\Writer\Stream;

return [
    'log' => [
        LoggerInterface::class . ' $errorLogger' => [
            'writers' => [
                [
                    'name' => Stream::class,
                    'priority' => Logger::ERR,
                    'options' => [
                        'stream' => 'php://stderr',
                    ],
                ],
            ],
        ],
    ],
];
