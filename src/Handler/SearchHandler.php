<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Search\SearchQueryRequest;
use FactorioItemBrowser\Api\Client\Response\Search\SearchQueryResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SearchResultsData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The handler for the search request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SearchHandler implements RequestHandlerInterface
{
    /**
     * The api client.
     * @var ApiClientInterface
     */
    protected $apiClient;

    /**
     * The mapper manager.
     * @var MapperManagerInterface
     */
    protected $mapperManager;

    /**
     * The number of recipes to return per result.
     * @var int
     */
    protected $numberOfRecipesPerResult;

    /**
     * Initializes the handler.
     * @param ApiClientInterface $apiClient
     * @param MapperManagerInterface $mapperManager
     * @param int $numberOfRecipesPerResult
     */
    public function __construct(
        ApiClientInterface $apiClient,
        MapperManagerInterface $mapperManager,
        int $numberOfRecipesPerResult
    ) {
        $this->apiClient = $apiClient;
        $this->mapperManager = $mapperManager;
        $this->numberOfRecipesPerResult = $numberOfRecipesPerResult;
    }

    /**
     * Handles the request.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws PortalApiServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        $query = (string) ($queryParams['query'] ?? '');
        $indexOfFirstResult = (int) ($queryParams['indexOfFirstResult'] ?? 0);
        $numberOfResults = (int) ($queryParams['numberOfResults'] ?? 0);

        $searchQueryResponse = $this->fetchData($query, $indexOfFirstResult, $numberOfResults);
        $searchResultsData = $this->createSearchResultsData($searchQueryResponse, $query);
        return new TransferResponse($searchResultsData);
    }

    /**
     * Fetches the data from the API.
     * @param string $query
     * @param int $indexOfFirstResult
     * @param int $numberOfResults
     * @return SearchQueryResponse
     * @throws PortalApiServerException
     */
    protected function fetchData(string $query, int $indexOfFirstResult, int $numberOfResults): SearchQueryResponse
    {
        $request = new SearchQueryRequest();
        $request->setQuery($query)
                ->setIndexOfFirstResult($indexOfFirstResult)
                ->setNumberOfResults($numberOfResults)
                ->setNumberOfRecipesPerResult($this->numberOfRecipesPerResult);

        try {
            /** @var SearchQueryResponse $response */
            $response = $this->apiClient->fetchResponse($request);
        } catch (ApiClientException $e) {
            throw new FailedApiRequestException($e);
        }
        return $response;
    }

    /**
     * Creates the results data from the search query response.
     * @param SearchQueryResponse $searchQueryResponse
     * @param string $query
     * @return SearchResultsData
     * @throws PortalApiServerException
     */
    protected function createSearchResultsData(
        SearchQueryResponse $searchQueryResponse,
        string $query
    ): SearchResultsData {
        $result = new SearchResultsData();
        $result->setQuery($query);
        try {
            $this->mapperManager->map($searchQueryResponse, $result);
        } catch (MapperException $e) {
            throw new MappingException($e);
        }
        return $result;
    }
}
