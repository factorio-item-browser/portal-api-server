<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\Mapper\DynamicMapperInterface;
use BluePsyduck\MapperManager\MapperManagerAwareInterface;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Entity\Recipe;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;

/**
 * The mapper of the generic entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @todo When mapping recipes, pay attention to expensive version as configured by the user.
 */
class GenericEntityMapper implements DynamicMapperInterface, MapperManagerAwareInterface
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
     * Returns whether the mapper supports the combination of source and destination object.
     * @param object $source
     * @param object $destination
     * @return bool
     */
    public function supports($source, $destination): bool
    {
        return $source instanceof GenericEntity && $destination instanceof EntityData;
    }

    /**
     * Maps the source object to the destination one.
     * @param GenericEntity $source
     * @param EntityData $destination
     */
    public function map($source, $destination): void
    {
        $destination->setType($source->getType())
                    ->setName($source->getName())
                    ->setLabel($source->getLabel());

        if ($source instanceof GenericEntityWithRecipes) {
            $destination->setRecipes(array_map([$this, 'mapRecipe'], $source->getRecipes()))
                        ->setNumberOfRecipes($source->getTotalNumberOfRecipes());
        }
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
