<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The object representing a recipe for crafting.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeData
{
    public float $craftingTime = 0.;
    /** @var array<RecipeItemData> */
    public array $ingredients = [];
    /** @var array<RecipeItemData> */
    public array $products = [];
    public bool $isExpensive = false;
}
