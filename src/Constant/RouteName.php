<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Constant;

/**
 * The interface holding the route names.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface RouteName
{
    /**
     * The route for fetching recipes having a certain ingredient.
     */
    public const ITEM_INGREDIENTS = 'item.ingredients';

    /**
     * The route for fetching recipes having a certain product.
     */
    public const ITEM_PRODUCTS = 'item.products';

    /**
     * The route for fetching the details of a recipe.
     */
    public const RECIPE_DETAILS = 'recipe.details';

    /**
     * The route for fetching the machines able to craft a recipe.
     */
    public const RECIPE_MACHINES = 'recipe.machines';

    /**
     * The route for fetching search results.
     */
    public const SEARCH = 'search';

    /**
     * The route for initializing the session.
     */
    public const SESSION_INIT = 'session.init';

    /**
     * The route for sending the sidebar entities to the server.
     */
    public const SIDEBAR_ENTITIES = 'sidebar.entities';

    /**
     * The route for fetching additional styles for icons.
     */
    public const STYLE_ICONS = 'style.icons';
}
