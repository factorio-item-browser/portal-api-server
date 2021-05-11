<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Constant;

/**
 * The interface holding the defaults of some values.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface Defaults
{
    public const SETTING_NAME = 'Vanilla';
    public const TEMPORARY_SETTING_NAME = 'Temporary';
    public const RECIPE_MODE = RecipeMode::HYBRID;
    public const LOCALE = 'en';
}
