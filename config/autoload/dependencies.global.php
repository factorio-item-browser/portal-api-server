<?php

/**
 * The configuration of the project dependencies.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
// phpcs:ignoreFile

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server;

use BluePsyduck\JmsSerializerFactory\JmsSerializerFactory;
use BluePsyduck\LaminasAutoWireFactory\AutoWireFactory;
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManagerInterface;
use FactorioItemBrowser\PortalApi\Server\Constant\ConfigKey;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\SerializerInterface;
use Mezzio\Middleware\ErrorResponseGenerator;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Roave\PsrContainerDoctrine\EntityManagerFactory;
use Roave\PsrContainerDoctrine\Migrations\ConfigurationLoaderFactory;
use Roave\PsrContainerDoctrine\Migrations\DependencyFactoryFactory;

use function BluePsyduck\LaminasAutoWireFactory\readConfig;

return [
    'dependencies' => [
        'aliases' => [
            ErrorResponseGenerator::class => Response\ErrorResponseGenerator::class,
        ],
        'factories' => [
            Command\CleanSessionsCommand::class => AutoWireFactory::class,

            Handler\InitHandler::class => AutoWireFactory::class,
            Handler\Item\IngredientsHandler::class => AutoWireFactory::class,
            Handler\Item\ProductsHandler::class => AutoWireFactory::class,
            Handler\ItemsHandler::class => AutoWireFactory::class,
            Handler\NotFoundHandler::class => AutoWireFactory::class,
            Handler\RandomHandler::class => AutoWireFactory::class,
            Handler\Recipe\DetailsHandler::class => AutoWireFactory::class,
            Handler\Recipe\MachinesHandler::class => AutoWireFactory::class,
            Handler\SearchHandler::class => AutoWireFactory::class,
            Handler\Setting\DeleteHandler::class => AutoWireFactory::class,
            Handler\Setting\DetailsHandler::class => AutoWireFactory::class,
            Handler\Setting\ListHandler::class => AutoWireFactory::class,
            Handler\Setting\ModsHandler::class => AutoWireFactory::class,
            Handler\Setting\SaveHandler::class => AutoWireFactory::class,
            Handler\Setting\ValidateHandler::class => AutoWireFactory::class,
            Handler\Sidebar\EntitiesHandler::class => AutoWireFactory::class,
            Handler\Style\IconsHandler::class => AutoWireFactory::class,
            Handler\Tooltip\ItemHandler::class => AutoWireFactory::class,
            Handler\Tooltip\RecipeHandler::class => AutoWireFactory::class,

            Helper\CombinationHelper::class => AutoWireFactory::class,
            Helper\CookieHelper::class => AutoWireFactory::class,
            Helper\IconsStyleFetcher::class => AutoWireFactory::class,
            Helper\RecipeSelector::class => AutoWireFactory::class,
            Helper\SidebarEntitiesHelper::class => AutoWireFactory::class,

            Mapper\GenericEntityMapper::class => AutoWireFactory::class,
            Mapper\ItemListResponseMapper::class => AutoWireFactory::class,
            Mapper\ItemRecipesMapper::class => AutoWireFactory::class,
            Mapper\MachineMapper::class => AutoWireFactory::class,
            Mapper\ModMapper::class => AutoWireFactory::class,
            Mapper\RecipeDetailsMapper::class => AutoWireFactory::class,
            Mapper\RecipeItemMapper::class => AutoWireFactory::class,
            Mapper\RecipeMachinesMapper::class => AutoWireFactory::class,
            Mapper\RecipeMapper::class => AutoWireFactory::class,
            Mapper\RecipeToEntityMapper::class => AutoWireFactory::class,
            Mapper\SearchQueryResponseMapper::class => AutoWireFactory::class,
            Mapper\SettingMapper::class => AutoWireFactory::class,
            Mapper\SidebarEntityDataMapper::class => AutoWireFactory::class,
            Mapper\SidebarEntityMapper::class => AutoWireFactory::class,

            Middleware\ApiClientMiddleware::class => AutoWireFactory::class,
            Middleware\CorsHeaderMiddleware::class => AutoWireFactory::class,
            Middleware\MetaMiddleware::class => AutoWireFactory::class,
            Middleware\RequestDeserializerMiddleware::class => AutoWireFactory::class,
            Middleware\ResponseSerializerMiddleware::class => AutoWireFactory::class,
            Middleware\SessionMiddleware::class => AutoWireFactory::class,

            Repository\CombinationRepository::class => AutoWireFactory::class,
            Repository\SettingRepository::class => AutoWireFactory::class,
            Repository\SidebarEntityRepository::class => AutoWireFactory::class,
            Repository\UserRepository::class => AutoWireFactory::class,

            Response\ErrorResponseGenerator::class => AutoWireFactory::class,

            // 3rd-party dependencies
            ConfigurationLoader::class => ConfigurationLoaderFactory::class,
            DependencyFactory::class => DependencyFactoryFactory::class,
            EntityManagerInterface::class => EntityManagerFactory::class,
            IdenticalPropertyNamingStrategy::class => AutoWireFactory::class,
            ImplicitOptionsMiddleware::class => Middleware\ImplicitOptionsMiddlewareFactory::class,

            // Auto-wire helpers
            SerializerInterface::class . ' $portalApiServerSerializer' => new JmsSerializerFactory(ConfigKey::MAIN, ConfigKey::SERIALIZER),

            'array $allowedOrigins' => readConfig(ConfigKey::MAIN, ConfigKey::ALLOWED_ORIGINS),
            'array $requestClassesByRoutes' => readConfig(ConfigKey::MAIN, ConfigKey::REQUEST_CLASSES_BY_ROUTES),

            'bool $isDebug' => readConfig('debug'),
            'bool $useSecureCookie' => readConfig(ConfigKey::MAIN, ConfigKey::SESSION_COOKIE_SECURE),

            'int $numberOfRecipesPerResult' => readConfig(ConfigKey::MAIN, ConfigKey::NUMBER_OF_RECIPES_PER_RESULT),

            'string $scriptVersion' => readConfig(ConfigKey::MAIN, ConfigKey::SCRIPT_VERSION),
            'string $sessionCookieDomain' => readConfig(ConfigKey::MAIN, ConfigKey::SESSION_COOKIE_DOMAIN),
            'string $sessionCookieLifeTime' => readConfig(ConfigKey::MAIN, ConfigKey::SESSION_COOKIE_LIFETIME),
            'string $sessionCookieName' => readConfig(ConfigKey::MAIN, ConfigKey::SESSION_COOKIE_NAME),
            'string $sessionCookiePath' => readConfig(ConfigKey::MAIN, ConfigKey::SESSION_COOKIE_PATH),
            'string $sessionLifeTime' => readConfig(ConfigKey::MAIN, ConfigKey::SESSION_LIFETIME),
            'string $temporarySettingLifeTime' => readConfig(ConfigKey::MAIN, ConfigKey::TEMPORARY_SETTING_LIFETIME),
            'string $version' => readConfig('version'),
        ],
    ],
];
