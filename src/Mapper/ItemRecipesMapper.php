<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use BluePsyduck\MapperManager\MapperManagerAwareInterface;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Entity\Recipe;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemRecipesData;

/**
 * The mapper of the item recipe responses.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemRecipesMapper implements StaticMapperInterface, MapperManagerAwareInterface
{
    /**
     * The mapper manager.
     * @var MapperManagerInterface
     */
    protected $mapperManager;

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
        return GenericEntityWithRecipes::class;
    }

    /**
     * Returns the destination class supported by this mapper.
     * @return string
     */
    public function getSupportedDestinationClass(): string
    {
        return ItemRecipesData::class;
    }

    /**
     * Maps the source object to the destination one.
     * @param GenericEntityWithRecipes $source
     * @param ItemRecipesData $destination
     */
    public function map($source, $destination): void
    {
        $destination->setType($source->getType())
                    ->setName($source->getName())
                    ->setLabel($source->getLabel())
                    ->setDescription($source->getDescription())
                    ->setResults(array_map([$this, 'mapRecipe'], $source->getRecipes()))
                    ->setNumberOfResults($source->getTotalNumberOfRecipes());
    }

    /**
     * Maps the recipe.
     * @param Recipe $recipe
     * @return EntityData
     * @throws MapperException
     */
    protected function mapRecipe(Recipe $recipe): EntityData
    {
        $entityData = new EntityData();
        $this->mapperManager->map($recipe, $entityData);
        return $entityData;
    }
}
