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
use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareFactory;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->get('/{type:item|fluid}/{name}/ingredients', Handler\Item\IngredientsHandler::class, RouteName::ITEM_INGREDIENTS);
    $app->get('/{type:item|fluid}/{name}/products', Handler\Item\ProductsHandler::class, RouteName::ITEM_PRODUCTS);
    $app->get('/recipe/{name}', Handler\Recipe\DetailsHandler::class, RouteName::RECIPE_DETAILS);
    $app->get('/search', Handler\SearchHandler::class, RouteName::SEARCH);
    $app->post('/style/icons', Handler\Style\IconsHandler::class, RouteName::STYLE_ICONS);
};
