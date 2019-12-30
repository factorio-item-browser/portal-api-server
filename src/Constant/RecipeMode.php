<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Constant;

use FactorioItemBrowser\Common\Constant\RecipeMode as CommonRecipeMode;

/**
 * The interface holding the values of the recipe mode.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface RecipeMode
{
    /**
     * The hybrid mode is used where both variants are displayed.
     */
    public const HYBRID = 'hybrid';

    /**
     * The normal mode is used, expensive variants are ignored.
     */
    public const NORMAL = CommonRecipeMode::NORMAL;

    /**
     * The expensive mode is used, normal variants get overwritten by expensive ones.
     */
    public const EXPENSIVE = CommonRecipeMode::EXPENSIVE;
}
