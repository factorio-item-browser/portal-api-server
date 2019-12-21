<?php

declare(strict_types=1);

/**
 * The configuration of the project dependencies.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\PortalApi\Server;

use BluePsyduck\ZendAutoWireFactory\AutoWireFactory;
use JMS\Serializer\SerializerInterface;

return [
    'dependencies' => [
        'factories' => [
            Handler\NotFoundHandler::class => AutoWireFactory::class,
            Handler\SearchHandler::class => AutoWireFactory::class,

            Mapper\GenericEntityMapper::class => AutoWireFactory::class,
            Mapper\RecipeItemMapper::class => AutoWireFactory::class,
            Mapper\RecipeMapper::class => AutoWireFactory::class,
            Mapper\SearchQueryResponseMapper::class => AutoWireFactory::class,

            Middleware\ResponseSerializerMiddleware::class => AutoWireFactory::class,

            // Auto-wire helpers
            SerializerInterface::class . ' $portalApiServerSerializer' => Serializer\SerializerFactory::class,
        ],
    ],
];
