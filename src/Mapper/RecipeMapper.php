<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\DynamicMapperInterface;
use BluePsyduck\MapperManager\MapperManagerAwareInterface;
use BluePsyduck\MapperManager\MapperManagerAwareTrait;
use FactorioItemBrowser\Api\Client\Transfer\Item;
use FactorioItemBrowser\Api\Client\Transfer\Recipe;
use FactorioItemBrowser\Common\Constant\RecipeMode;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeItemData;

/**
 * The mapper of the recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements DynamicMapperInterface<Recipe, RecipeData>
 */
class RecipeMapper implements DynamicMapperInterface, MapperManagerAwareInterface
{
    use MapperManagerAwareTrait;

    public function supports(object $source, object $destination): bool
    {
        return $source instanceof Recipe && $destination instanceof RecipeData;
    }

    /**
     * @param Recipe $source
     * @param RecipeData $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->craftingTime = $source->craftingTime;
        $destination->ingredients = array_map([$this, 'mapItem'], $source->ingredients);
        $destination->products = array_map([$this, 'mapItem'], $source->products);
        $destination->isExpensive = $source->mode === RecipeMode::EXPENSIVE;
    }

    private function mapItem(Item $item): RecipeItemData
    {
        return $this->mapperManager->map($item, new RecipeItemData());
    }
}
