<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use BluePsyduck\MapperManager\MapperManagerAwareInterface;
use BluePsyduck\MapperManager\MapperManagerAwareTrait;
use FactorioItemBrowser\Api\Client\Response\Search\SearchQueryResponse;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SearchResultsData;

/**
 * The mapper of the search query response.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<SearchQueryResponse, SearchResultsData>
 */
class SearchQueryResponseMapper implements StaticMapperInterface, MapperManagerAwareInterface
{
    use MapperManagerAwareTrait;

    public function getSupportedSourceClass(): string
    {
        return SearchQueryResponse::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return SearchResultsData::class;
    }

    /**
     * @param SearchQueryResponse $source
     * @param SearchResultsData $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->results = array_map([$this, 'mapEntity'], $source->results);
        $destination->numberOfResults = $source->totalNumberOfResults;
    }

    private function mapEntity(GenericEntityWithRecipes $entity): EntityData
    {
        return $this->mapperManager->map($entity, new EntityData());
    }
}
