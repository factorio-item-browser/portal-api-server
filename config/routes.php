<?php

declare(strict_types=1);

/**
 * The file providing the routes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\PortalApi\Server;

use FactorioItemBrowser\PortalApi\Server\Constant\RouteName;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->get('/{type:item|fluid}/{name}/ingredients', Handler\Item\IngredientsHandler::class, RouteName::ITEM_INGREDIENTS);
    $app->get('/{type:item|fluid}/{name}/products', Handler\Item\ProductsHandler::class, RouteName::ITEM_PRODUCTS);
    $app->get('/random', Handler\RandomHandler::class, RouteName::RANDOM);
    $app->get('/recipe/{name}', Handler\Recipe\DetailsHandler::class, RouteName::RECIPE_DETAILS);
    $app->get('/recipe/{name}/machines', Handler\Recipe\MachinesHandler::class, RouteName::RECIPE_MACHINES);
    $app->get('/search', Handler\SearchHandler::class, RouteName::SEARCH);
    $app->get('/session/init', Handler\Session\InitHandler::class, RouteName::SESSION_INIT);
    $app->get('/settings', Handler\Settings\ListHandler::class, RouteName::SETTINGS_LIST);
    $app->get('/settings/{setting-id}', Handler\Settings\DetailsHandler::class, RouteName::SETTINGS_DETAILS);
    $app->post('/settings/{setting-id}', Handler\Settings\SaveHandler::class, RouteName::SETTINGS_SAVE);
    $app->post('/sidebar/entities', Handler\Sidebar\EntitiesHandler::class, RouteName::SIDEBAR_ENTITIES);
    $app->post('/style/icons', Handler\Style\IconsHandler::class, RouteName::STYLE_ICONS);
    $app->get('/tooltip/{type:item|fluid}/{name}', Handler\Tooltip\ItemHandler::class, RouteName::TOOLTIP_ITEM);
    $app->get('/tooltip/recipe/{name}', Handler\Tooltip\RecipeHandler::class, RouteName::TOOLTIP_RECIPE);
};
