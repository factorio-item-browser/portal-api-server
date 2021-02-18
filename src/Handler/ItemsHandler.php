<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Request\Item\ItemListRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemListResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
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
    private ClientInterface $apiClient;
    private MapperManagerInterface $mapperManager;

    public function __construct(ClientInterface $apiClient, MapperManagerInterface $mapperManager)
    {
        $this->apiClient = $apiClient;
        $this->mapperManager = $mapperManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws PortalApiServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $numberOfResults = (int) ($queryParams['numberOfResults'] ?? 0);
        $indexOfFirstResult = (int) ($queryParams['indexOfFirstResult'] ?? 0);

        $items = $this->fetchData($indexOfFirstResult, $numberOfResults);
        $response = $this->mapperManager->map($items, new ItemListData());
        return new TransferResponse($response);
    }

    /**
     * @param int $numberOfResults
     * @param int $indexOfFirstResult
     * @return ItemListResponse
     * @throws PortalApiServerException
     */
    private function fetchData(int $indexOfFirstResult, int $numberOfResults): ItemListResponse
    {
        $request = new ItemListRequest();
        $request->indexOfFirstResult = $indexOfFirstResult;
        $request->numberOfResults = $numberOfResults;
        $request->numberOfRecipesPerResult = 0;

        try {
            /** @var ItemListResponse $response */
            $response = $this->apiClient->sendRequest($request)->wait();
            return $response;
        } catch (ClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }
}
