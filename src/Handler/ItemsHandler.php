<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Item\ItemListRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemListResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemListData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The handler for providing the full list of items.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemsHandler implements RequestHandlerInterface
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
    public function __construct(
        ApiClientInterface $apiClient,
        MapperManagerInterface $mapperManager
    ) {
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
        $queryParams = $request->getQueryParams();
        $numberOfResults = (int) ($queryParams['numberOfResults'] ?? 0);
        $indexOfFirstResult = (int) ($queryParams['indexOfFirstResult'] ?? 0);

        $items = $this->fetchData($numberOfResults, $indexOfFirstResult);
        return new TransferResponse($this->createItemListData($items));
    }

    /**
     * Fetches the data from the API.
     * @param int $numberOfResults
     * @param int $indexOfFirstResult
     * @return ItemListResponse
     * @throws PortalApiServerException
     */
    protected function fetchData(int $numberOfResults, int $indexOfFirstResult): ItemListResponse
    {
        $request = new ItemListRequest();
        $request->setNumberOfResults($numberOfResults)
                ->setIndexOfFirstResult($indexOfFirstResult)
                ->setNumberOfRecipesPerResult(0);

        try {
            /** @var ItemListResponse $response */
            $response = $this->apiClient->fetchResponse($request);
            return $response;
        } catch (ApiClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }

    /**
     * Creates the entity data transfer from the entity.
     * @param ItemListResponse $response
     * @return ItemListData
     * @throws PortalApiServerException
     */
    protected function createItemListData(ItemListResponse $response): ItemListData
    {
        $itemListData = new ItemListData();
        try {
            $this->mapperManager->map($response, $itemListData);
        } catch (MapperException $e) {
            throw new MappingException($e);
        }
        return $itemListData;
    }
}
