<?php

/**
 * The file providing the routes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
// phpcs:ignoreFile

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server;

use FactorioItemBrowser\PortalApi\Server\Constant\RouteName;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->post('/init', Handler\InitHandler::class, RouteName::INIT);
    $app->get('/{type:item|fluid}/{name}/ingredients', Handler\Item\IngredientsHandler::class, RouteName::ITEM_INGREDIENTS);
    $app->get('/{type:item|fluid}/{name}/products', Handler\Item\ProductsHandler::class, RouteName::ITEM_PRODUCTS);
    $app->get('/items', Handler\ItemsHandler::class, RouteName::ITEMS);
    $app->get('/random', Handler\RandomHandler::class, RouteName::RANDOM);
    $app->get('/recipe/{name}', Handler\Recipe\DetailsHandler::class, RouteName::RECIPE_DETAILS);
    $app->get('/recipe/{name}/machines', Handler\Recipe\MachinesHandler::class, RouteName::RECIPE_MACHINES);
    $app->get('/search', Handler\SearchHandler::class, RouteName::SEARCH);

    $app->get('/settings', Handler\Setting\ListHandler::class, RouteName::SETTING_LIST);
    $app->post('/setting/validate', Handler\Setting\ValidateHandler::class, RouteName::SETTING_VALIDATE);
    $app->delete('/setting/{combination-id:[0-9a-f-]{36}}', Handler\Setting\DeleteHandler::class, RouteName::SETTING_DELETE);
    $app->get('/setting/{combination-id:[0-9a-f-]{36}}', Handler\Setting\DetailsHandler::class, RouteName::SETTING_DETAILS);
    $app->put('/setting/{combination-id:[0-9a-f-]{36}}', Handler\Setting\SaveHandler::class, RouteName::SETTING_SAVE);
    $app->get('/setting/{combination-id:[0-9a-f-]{36}}/mods', Handler\Setting\ModsHandler::class, RouteName::SETTING_MODS);

    $app->put('/sidebar/entities', Handler\Sidebar\EntitiesHandler::class, RouteName::SIDEBAR_ENTITIES);
    $app->post('/style/icons', Handler\Style\IconsHandler::class, RouteName::STYLE_ICONS);
    $app->get('/tooltip/{type:item|fluid}/{name}', Handler\Tooltip\ItemHandler::class, RouteName::TOOLTIP_ITEM);
    $app->get('/tooltip/recipe/{name}', Handler\Tooltip\RecipeHandler::class, RouteName::TOOLTIP_RECIPE);
};
