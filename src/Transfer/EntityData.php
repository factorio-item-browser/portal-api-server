<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The object representing the data of an entity including its recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class EntityData
{
    public string $type = '';
    public string $name = '';
    public string $label = '';
    /** @var array<RecipeData> */
    public array $recipes = [];
    public int $numberOfRecipes = 0;
}
