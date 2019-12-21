<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use BluePsyduck\MapperManager\MapperManagerAwareInterface;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Response\Search\SearchQueryResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SearchResultData;

/**
 * The mapper of the search query response.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SearchQueryResponseMapper implements StaticMapperInterface, MapperManagerAwareInterface
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
        return SearchQueryResponse::class;
    }

    /**
     * Returns the destination class supported by this mapper.
     * @return string
     */
    public function getSupportedDestinationClass(): string
    {
        return SearchResultData::class;
    }

    /**
     * Maps the source object to the destination one.
     * @param SearchQueryResponse $source
     * @param SearchResultData $destination
     */
    public function map($source, $destination): void
    {
        $destination->setResults(array_map(function(GenericEntityWithRecipes $genericEntityWithRecipes): EntityData {
            $entityData = new EntityData();
            $this->mapperManager->map($genericEntityWithRecipes, $entityData);
            return $entityData;
        }, $source->getResults()));
        $destination->setNumberOfResults($source->getTotalNumberOfResults());
    }
}
