<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Request\Search\SearchQueryRequest;
use FactorioItemBrowser\Api\Client\Response\Search\SearchQueryResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
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
    private ClientInterface $apiClient;
    private MapperManagerInterface $mapperManager;
    private int $numberOfRecipesPerResult;

    public function __construct(
        ClientInterface $apiClient,
        MapperManagerInterface $mapperManager,
        int $numberOfRecipesPerResult
    ) {
        $this->apiClient = $apiClient;
        $this->mapperManager = $mapperManager;
        $this->numberOfRecipesPerResult = $numberOfRecipesPerResult;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws PortalApiServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        $query = (string) ($queryParams['query'] ?? '');
        $indexOfFirstResult = (int) ($queryParams['indexOfFirstResult'] ?? 0);
        $numberOfResults = (int) ($queryParams['numberOfResults'] ?? 10);

        $searchQueryResponse = $this->fetchData($query, $indexOfFirstResult, $numberOfResults);

        $response = $this->mapperManager->map($searchQueryResponse, new SearchResultsData());
        $response->query = $query;
        return new TransferResponse($response);
    }

    /**
     * @param string $query
     * @param int $indexOfFirstResult
     * @param int $numberOfResults
     * @return SearchQueryResponse
     * @throws PortalApiServerException
     */
    private function fetchData(string $query, int $indexOfFirstResult, int $numberOfResults): SearchQueryResponse
    {
        $request = new SearchQueryRequest();
        $request->query = $query;
        $request->indexOfFirstResult = $indexOfFirstResult;
        $request->numberOfResults = $numberOfResults;
        $request->numberOfRecipesPerResult = $this->numberOfRecipesPerResult;

        try {
            /** @var SearchQueryResponse $response */
            $response = $this->apiClient->sendRequest($request)->wait();
            return $response;
        } catch (ClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }
}
