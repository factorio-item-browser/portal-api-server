<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use BluePsyduck\MapperManager\MapperManagerAwareInterface;
use BluePsyduck\MapperManager\MapperManagerAwareTrait;
use FactorioItemBrowser\Api\Client\Transfer\Recipe;
use FactorioItemBrowser\Api\Client\Transfer\RecipeWithExpensiveVersion;
use FactorioItemBrowser\PortalApi\Server\Helper\RecipeSelector;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeDetailsData;

/**
 * The mapper of the recipe details.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<RecipeWithExpensiveVersion, RecipeDetailsData>
 */
class RecipeDetailsMapper implements StaticMapperInterface, MapperManagerAwareInterface
{
    use MapperManagerAwareTrait;

    private RecipeSelector $recipeSelector;

    public function __construct(RecipeSelector $recipeSelector)
    {
        $this->recipeSelector = $recipeSelector;
    }

    public function getSupportedSourceClass(): string
    {
        return RecipeWithExpensiveVersion::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return RecipeDetailsData::class;
    }

    /**
     * @param RecipeWithExpensiveVersion $source
     * @param RecipeDetailsData $destination
     */
    public function map(object $source, object $destination): void
    {
        $recipes = $this->recipeSelector->select($source);

        $destination->name = $source->name;
        $destination->label = $source->label;
        $destination->description = $source->description;
        $destination->recipe = $this->mapRecipe($recipes[0] ?? null);
        $destination->expensiveRecipe = $this->mapRecipe($recipes[1] ?? null);
    }

    private function mapRecipe(?Recipe $recipe): ?RecipeData
    {
        if ($recipe === null) {
            return null;
        }
        return $this->mapperManager->map($recipe, new RecipeData());
    }
}
