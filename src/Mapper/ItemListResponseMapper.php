<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Client\Response\Item\ItemListResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemListData;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemMetaData;

/**
 * The mapper of the item list response.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<ItemListResponse, ItemListData>
 */
class ItemListResponseMapper implements StaticMapperInterface
{
    public function getSupportedSourceClass(): string
    {
        return ItemListResponse::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return ItemListData::class;
    }

    /**
     * @param ItemListResponse $source
     * @param ItemListData $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->results = array_map([$this, 'mapItem'], $source->items);
        $destination->numberOfResults = $source->totalNumberOfResults;
    }

    /**
     * @param GenericEntity $item
     * @return ItemMetaData
     */
    protected function mapItem(GenericEntity $item): ItemMetaData
    {
        $result = new ItemMetaData();
        $result->type = $item->type;
        $result->name = $item->name;
        return $result;
    }
}
