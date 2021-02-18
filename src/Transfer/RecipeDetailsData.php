<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The object representing the details of a recipe.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeDetailsData
{
    public string $name = '';
    public string $label = '';
    public string $description = '';
    public ?RecipeData $recipe = null;
    public ?RecipeData $expensiveRecipe = null;
}
