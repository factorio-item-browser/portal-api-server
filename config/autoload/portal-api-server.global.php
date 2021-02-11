<?php

/**
 * The configuration of the Portal API server itself.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server;

use BluePsyduck\JmsSerializerFactory\Constant\ConfigKey as JmsConfigKey;
use FactorioItemBrowser\PortalApi\Server\Constant\ConfigKey;
use FactorioItemBrowser\PortalApi\Server\Constant\RouteName;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;

return [
    ConfigKey::MAIN => [
        ConfigKey::NUMBER_OF_RECIPES_PER_RESULT => 3,
        ConfigKey::REQUEST_CLASSES_BY_ROUTES => [
            RouteName::SIDEBAR_ENTITIES => sprintf('array<%s>', Transfer\SidebarEntityData::class),
            RouteName::STYLE_ICONS => Transfer\NamesByTypes::class,
        ],
        ConfigKey::SERIALIZER => [
            JmsConfigKey::METADATA_DIRS => [
                __NAMESPACE__ => 'config/serializer',
            ],
            JmsConfigKey::PROPERTY_NAMING_STRATEGY => IdenticalPropertyNamingStrategy::class,
        ],
    ],
];
