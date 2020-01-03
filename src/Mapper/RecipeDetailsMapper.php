<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use BluePsyduck\MapperManager\MapperManagerAwareInterface;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\Recipe;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\PortalApi\Server\Helper\RecipeSelector;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeDetailsData;

/**
 * The mapper of the recipe details.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeDetailsMapper implements StaticMapperInterface, MapperManagerAwareInterface
{
    /**
     * The mapper manager.
     * @var MapperManagerInterface
     */
    protected $mapperManager;

    /**
     * The recipe selector.
     * @var RecipeSelector
     */
    protected $recipeSelector;

    /**
     * Initializes the mapper.
     * @param RecipeSelector $recipeSelector
     */
    public function __construct(RecipeSelector $recipeSelector)
    {
        $this->recipeSelector = $recipeSelector;
    }

    /**
     * Sets the mapper manager.
     * @param MapperManagerInterface $mapperManager
     */
    public function setMapperManager(MapperManagerInterface $mapperManager): void
    {
        $this->mapperManager = $mapperManager;
    }

    /**
     * Returns the source class supported by this mapper.
     * @return string
     */
    public function getSupportedSourceClass(): string
    {
        return RecipeWithExpensiveVersion::class;
    }

    /**
     * Returns the destination class supported by this mapper.
     * @return string
     */
    public function getSupportedDestinationClass(): string
    {
        return RecipeDetailsData::class;
    }

    /**
     * Maps the source object to the destination one.
     * @param RecipeWithExpensiveVersion $source
     * @param RecipeDetailsData $destination
     * @throws MapperException
     */
    public function map($source, $destination): void
    {
        @list($recipe, $expensiveRecipe) = $this->recipeSelector->select($source);

        $destination->setName($source->getName())
                    ->setLabel($source->getLabel())
                    ->setDescription($source->getDescription())
                    ->setRecipe($this->mapRecipe($recipe))
                    ->setExpensiveRecipe($this->mapRecipe($expensiveRecipe));
    }

    /**
     * Maps the recipe.
     * @param Recipe|null $recipe
     * @return RecipeData|null
     * @throws MapperException
     */
    protected function mapRecipe(?Recipe $recipe): ?RecipeData
    {
        if ($recipe === null) {
            return null;
        }

        $recipeData = new RecipeData();
        $this->mapperManager->map($recipe, $recipeData);
        return $recipeData;
    }
}
