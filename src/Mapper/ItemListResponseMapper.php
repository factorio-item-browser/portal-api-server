<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Client\Response\Item\ItemListResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemListData;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemMetaData;

/**
 * The mapper of the item list response.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemListResponseMapper implements StaticMapperInterface
{
    /**
     * Returns the source class supported by this mapper.
     * @return string
     */
    public function getSupportedSourceClass(): string
    {
        return ItemListResponse::class;
    }

    /**
     * Returns the destination class supported by this mapper.
     * @return string
     */
    public function getSupportedDestinationClass(): string
    {
        return ItemListData::class;
    }

    /**
     * Maps the source object to the destination one.
     * @param ItemListResponse $source
     * @param ItemListData $destination
     */
    public function map($source, $destination): void
    {
        $destination->setResults(array_map([$this, 'mapItem'], $source->getItems()))
                    ->setNumberOfResults($source->getTotalNumberOfResults());
    }

    /**
     * Maps an item of the list.
     * @param GenericEntity $item
     * @return ItemMetaData
     */
    protected function mapItem(GenericEntity $item): ItemMetaData
    {
        $result = new ItemMetaData();
        $result->setType($item->getType())
               ->setName($item->getName());
        return $result;
    }
}
