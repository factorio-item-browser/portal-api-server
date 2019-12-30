<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\Mapper\DynamicMapperInterface;
use BluePsyduck\MapperManager\MapperManagerAwareInterface;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\Recipe;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\PortalApi\Server\Helper\RecipeSelector;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;

/**
 * The mapper for mapping recipes to entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeToEntityMapper implements DynamicMapperInterface, MapperManagerAwareInterface
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
     * Returns whether the mapper supports the combination of source and destination object.
     * @param object $source
     * @param object $destination
     * @return bool
     */
    public function supports($source, $destination): bool
    {
        return $source instanceof Recipe && $destination instanceof EntityData;
    }

    /**
     * Maps the source object to the destination one.
     * @param Recipe $source
     * @param EntityData $destination
     */
    public function map($source, $destination): void
    {
        $recipes = $this->recipeSelector->select($source);

        $destination->setType(EntityType::RECIPE)
                    ->setName($source->getName())
                    ->setLabel($source->getLabel())
                    ->setRecipes(array_map([$this, 'mapRecipe'], $recipes))
                    ->setNumberOfRecipes(1);
    }

    /**
     * Maps the recipe instance.
     * @param Recipe $recipe
     * @return RecipeData
     * @throws MapperException
     */
    protected function mapRecipe(Recipe $recipe): RecipeData
    {
        $recipeData = new RecipeData();
        $this->mapperManager->map($recipe, $recipeData);
        return $recipeData;
    }
}
