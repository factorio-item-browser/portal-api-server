<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\Mapper\DynamicMapperInterface;
use BluePsyduck\MapperManager\MapperManagerAwareInterface;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\Item;
use FactorioItemBrowser\Api\Client\Entity\Recipe;
use FactorioItemBrowser\Common\Constant\RecipeMode;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeItemData;

/**
 * The mapper of the recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeMapper implements DynamicMapperInterface, MapperManagerAwareInterface
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
        return $source instanceof Recipe && $destination instanceof RecipeData;
    }

    /**
     * Maps the source object to the destination one.
     * @param Recipe $source
     * @param RecipeData $destination
     */
    public function map($source, $destination): void
    {
        $destination->setCraftingTime($source->getCraftingTime())
                    ->setIngredients(array_map([$this, 'mapItem'], $source->getIngredients()))
                    ->setProducts(array_map([$this, 'mapItem'], $source->getProducts()))
                    ->setIsExpensive($source->getMode() === RecipeMode::EXPENSIVE);
    }

    /**
     * Maps the item of a recipe.
     * @param Item $item
     * @return RecipeItemData
     * @throws MapperException
     */
    protected function mapItem(Item $item): RecipeItemData
    {
        $recipeItemData = new RecipeItemData();
        $this->mapperManager->map($item, $recipeItemData);
        return $recipeItemData;
    }
}
