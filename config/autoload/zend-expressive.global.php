<?php

declare(strict_types=1);

/**
 * The configuration file for Zend Expressive.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\PortalApi\Server;

use Zend\ConfigAggregator\ConfigAggregator;

return [
    ConfigAggregator::ENABLE_CACHE => true,
    'debug' => false,
    'version' => '1.0.0',
];
