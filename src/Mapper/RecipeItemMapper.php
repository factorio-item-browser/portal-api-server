<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Api\Client\Transfer\Item;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeItemData;

/**
 * The mapper of the items of recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<Item, RecipeItemData>
 */
class RecipeItemMapper implements StaticMapperInterface
{
    public function getSupportedSourceClass(): string
    {
        return Item::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return RecipeItemData::class;
    }

    /**
     * @param Item $source
     * @param RecipeItemData $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->type = $source->type;
        $destination->name = $source->name;
        $destination->label = $source->label;
        $destination->amount = $source->amount;
    }
}
