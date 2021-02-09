<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use BluePsyduck\MapperManager\MapperManagerAwareInterface;
use BluePsyduck\MapperManager\MapperManagerAwareTrait;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Transfer\Recipe;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemRecipesData;

/**
 * The mapper of the item recipe responses.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<GenericEntityWithRecipes, ItemRecipesData>
 */
class ItemRecipesMapper implements StaticMapperInterface, MapperManagerAwareInterface
{
    use MapperManagerAwareTrait;

    public function getSupportedSourceClass(): string
    {
        return GenericEntityWithRecipes::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return ItemRecipesData::class;
    }

    /**
     * @param GenericEntityWithRecipes $source
     * @param ItemRecipesData $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->type = $source->type;
        $destination->name = $source->name;
        $destination->label = $source->label;
        $destination->description = $source->description;
        $destination->results = array_map([$this, 'mapRecipe'], $source->recipes);
        $destination->numberOfResults = $source->totalNumberOfRecipes;
    }

    private function mapRecipe(Recipe $recipe): EntityData
    {
        return $this->mapperManager->map($recipe, new EntityData());
    }
}
