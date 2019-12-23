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
     * The route for fetching search results.
     */
    public const SEARCH = 'search';

    /**
     * The route for fetching additional styles for icons.
     */
    public const STYLE_ICONS = 'style.icons';
}
