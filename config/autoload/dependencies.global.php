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
            Handler\Item\IngredientsHandler::class => AutoWireFactory::class,
            Handler\Item\ProductsHandler::class => AutoWireFactory::class,
            Handler\NotFoundHandler::class => AutoWireFactory::class,
            Handler\SearchHandler::class => AutoWireFactory::class,
            Handler\Style\IconsHandler::class => AutoWireFactory::class,

            Helper\IconsStyleBuilder::class => AutoWireFactory::class,
            Helper\RecipeSelector::class => AutoWireFactory::class,

            Mapper\GenericEntityMapper::class => AutoWireFactory::class,
            Mapper\ItemRecipesMapper::class => AutoWireFactory::class,
            Mapper\RecipeItemMapper::class => AutoWireFactory::class,
            Mapper\RecipeMapper::class => AutoWireFactory::class,
            Mapper\RecipeToEntityMapper::class => AutoWireFactory::class,
            Mapper\SearchQueryResponseMapper::class => AutoWireFactory::class,

            Middleware\ApiClientMiddleware::class => AutoWireFactory::class,
            Middleware\CorsHeaderMiddleware::class => AutoWireFactory::class,
            Middleware\MetaMiddleware::class => AutoWireFactory::class,
            Middleware\ResponseSerializerMiddleware::class => AutoWireFactory::class,

            Response\ErrorResponseGenerator::class => AutoWireFactory::class,

            // Auto-wire helpers
            SerializerInterface::class . ' $portalApiServerSerializer' => Serializer\SerializerFactory::class,

            'array $allowedOrigins' => readConfig(ConfigKey::PROJECT, ConfigKey::PORTAL_API_SERVER, ConfigKey::ALLOWED_ORIGINS),
            'bool $isDebug' => readConfig('debug'),
            'int $numberOfRecipesPerResult' => readConfig(ConfigKey::PROJECT, ConfigKey::PORTAL_API_SERVER, ConfigKey::NUMBER_OF_RECIPES_PER_RESULT),
            'string $version' => readConfig('version'),
        ],
    ],
];
