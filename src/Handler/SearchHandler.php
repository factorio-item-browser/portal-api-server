<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Search\SearchQueryRequest;
use FactorioItemBrowser\Api\Client\Response\Search\SearchQueryResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SearchResultData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 *
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
     * Initializes the handler.
     * @param ApiClientInterface $apiClient
     * @param MapperManagerInterface $mapperManager
     */
    public function __construct(ApiClientInterface $apiClient, MapperManagerInterface $mapperManager)
    {
        $this->apiClient = $apiClient;
        $this->mapperManager = $mapperManager;
    }

    /**
     * Handles the request.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws PortalApiServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // @todo Move to middleware.
        $this->apiClient->setModNames(['base']);
        $this->apiClient->setLocale('de');

        $searchQueryResponse = $this->fetchData(
            $request->getQueryParams()['query'] ?? '',
            (int) ($request->getQueryParams()['indexOfFirstResult'] ?? 0),
            (int) ($request->getQueryParams()['numberOfResults'] ?? 0),
        );
        $result = $this->createResultData($searchQueryResponse);

        return new TransferResponse($result);
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
                ->setNumberOfResults($numberOfResults);
        //        ->setNumberOfRecipesPerResult() // @todo Add config value

        try {
            /** @var SearchQueryResponse $response */
            $response = $this->apiClient->fetchResponse($request);
        } catch (ApiClientException $e) {
            throw new PortalApiServerException('abc'); // @todo Add exception
        }
        return $response;
    }

    /**
     * Creates the result data from the search query response.
     * @param SearchQueryResponse $searchQueryResponse
     * @return SearchResultData
     * @throws PortalApiServerException
     */
    protected function createResultData(SearchQueryResponse $searchQueryResponse): SearchResultData
    {
        $result = new SearchResultData();
        try {
            $this->mapperManager->map($searchQueryResponse, $result);
        } catch (MapperException $e) {
            throw new PortalApiServerException('abc'); // @todo Add exception
        }
        return $result;
    }
}
