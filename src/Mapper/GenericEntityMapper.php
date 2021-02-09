<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\DynamicMapperInterface;
use BluePsyduck\MapperManager\MapperManagerAwareInterface;
use BluePsyduck\MapperManager\MapperManagerAwareTrait;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Transfer\Recipe;
use FactorioItemBrowser\PortalApi\Server\Helper\RecipeSelector;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;

/**
 * The mapper of the generic entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements DynamicMapperInterface<GenericEntity, EntityData>
 */
class GenericEntityMapper implements DynamicMapperInterface, MapperManagerAwareInterface
{
    use MapperManagerAwareTrait;

    private RecipeSelector $recipeSelector;

    public function __construct(RecipeSelector $recipeSelector)
    {
        $this->recipeSelector = $recipeSelector;
    }

    public function supports(object $source, object $destination): bool
    {
        return ($source instanceof GenericEntity && !$source instanceof Recipe)
            && $destination instanceof EntityData;
    }

    /**
     * @param GenericEntity $source
     * @param EntityData $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->type = $source->type;
        $destination->name = $source->name;
        $destination->label = $source->label;

        if ($source instanceof GenericEntityWithRecipes) {
            $recipes = $this->recipeSelector->selectArray($source->recipes);
            $destination->recipes = array_map([$this, 'mapRecipe'], $recipes);
            $destination->numberOfRecipes = $source->totalNumberOfRecipes;
        }
    }

    private function mapRecipe(Recipe $recipe): RecipeData
    {
        return $this->mapperManager->map($recipe, new RecipeData());
    }
}
