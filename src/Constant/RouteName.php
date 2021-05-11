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
     * The route for initializing the session.
     */
    public const INIT = 'init';

    /**
     * The route for fetching recipes having a certain ingredient.
     */
    public const ITEM_INGREDIENTS = 'item.ingredients';

    /**
     * The route for fetching recipes having a certain product.
     */
    public const ITEM_PRODUCTS = 'item.products';

    /**
     * The route for fetching a list of all items.
     */
    public const ITEMS = 'items';

    /**
     * The route for fetching random items.
     */
    public const RANDOM = 'random';

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
     * The route for deleting a setting.
     */
    public const SETTING_DELETE = 'setting.delete';

    /**
     * The route for requesting the details of a setting.
     */
    public const SETTING_DETAILS = 'setting.details';

    /**
     * The route for requesting the settings list.
     */
    public const SETTING_LIST = 'setting.list';

    /**
     * The route for requesting the mods of a setting.
     */
    public const SETTING_MODS = 'setting.mods';

    /**
     * The route for saving changes of a setting.
     */
    public const SETTING_SAVE = 'setting.save';

    /**
     * The route for requesting the status of the current setting.
     */
    public const SETTING_VALIDATE = 'setting.validate';

    /**
     * The route for sending the sidebar entities to the server.
     */
    public const SIDEBAR_ENTITIES = 'sidebar.entities';

    /**
     * The route for fetching additional styles for icons.
     */
    public const STYLE_ICONS = 'style.icons';

    /**
     * The route for fetching the tooltip data of an item or fluid.
     */
    public const TOOLTIP_ITEM = 'tooltip.item';

    /**
     * The route for fetching the tooltip data of a recipe.
     */
    public const TOOLTIP_RECIPE = 'tooltip.recipe';
}
