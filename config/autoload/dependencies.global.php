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
use FactorioItemBrowser\PortalApi\Server\Constant\ConfigKey;
use JMS\Serializer\SerializerInterface;
use Zend\Expressive\Middleware\ErrorResponseGenerator;
use function BluePsyduck\ZendAutoWireFactory\readConfig;

return [
    'dependencies' => [
        'aliases' => [
            ErrorResponseGenerator::class => Response\ErrorResponseGenerator::class,
        ],
        'factories' => [
            Handler\NotFoundHandler::class => AutoWireFactory::class,
            Handler\SearchHandler::class => AutoWireFactory::class,
            Handler\Style\IconsHandler::class => AutoWireFactory::class,

            Helper\IconsStyleBuilder::class => AutoWireFactory::class,

            Mapper\GenericEntityMapper::class => AutoWireFactory::class,
            Mapper\RecipeItemMapper::class => AutoWireFactory::class,
            Mapper\RecipeMapper::class => AutoWireFactory::class,
            Mapper\SearchQueryResponseMapper::class => AutoWireFactory::class,

            Middleware\CorsHeaderMiddleware::class => AutoWireFactory::class,
            Middleware\MetaMiddleware::class => AutoWireFactory::class,
            Middleware\ResponseSerializerMiddleware::class => AutoWireFactory::class,

            Response\ErrorResponseGenerator::class => AutoWireFactory::class,

            // Auto-wire helpers
            SerializerInterface::class . ' $portalApiServerSerializer' => Serializer\SerializerFactory::class,

            'array $allowedOrigins' => readConfig(ConfigKey::PROJECT, ConfigKey::PORTAL_API_SERVER),
            'bool $isDebug' => readConfig('debug'),
            'string $version' => readConfig('version'),
        ],
    ],
];
