<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\DynamicMapperInterface;
use BluePsyduck\MapperManager\MapperManagerAwareInterface;
use BluePsyduck\MapperManager\MapperManagerAwareTrait;
use FactorioItemBrowser\Api\Client\Transfer\Recipe;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\PortalApi\Server\Helper\RecipeSelector;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;

/**
 * The mapper for mapping recipes to entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements DynamicMapperInterface<Recipe, EntityData>
 */
class RecipeToEntityMapper implements DynamicMapperInterface, MapperManagerAwareInterface
{
    use MapperManagerAwareTrait;

    private RecipeSelector $recipeSelector;

    public function __construct(RecipeSelector $recipeSelector)
    {
        $this->recipeSelector = $recipeSelector;
    }

    public function supports(object $source, object $destination): bool
    {
        return $source instanceof Recipe && $destination instanceof EntityData;
    }

    /**
     * @param Recipe $source
     * @param EntityData $destination
     */
    public function map(object $source, object $destination): void
    {
        $recipes = $this->recipeSelector->select($source);

        $destination->type = EntityType::RECIPE;
        $destination->name = $source->name;
        $destination->label = $source->label;
        $destination->recipes = array_map([$this, 'mapRecipe'], $recipes);
        $destination->numberOfRecipes = 1;
    }

    private function mapRecipe(Recipe $recipe): RecipeData
    {
        return $this->mapperManager->map($recipe, new RecipeData());
    }
}
