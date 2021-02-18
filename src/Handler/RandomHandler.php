<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Request\Item\ItemRandomRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemRandomResponse;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The handler for returning random items.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RandomHandler implements RequestHandlerInterface
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
        $numberOfResults = (int) ($queryParams['numberOfResults'] ?? 0);

        $items = $this->fetchData($numberOfResults);
        return new TransferResponse(array_map([$this, 'mapItem'], $items));
    }

    /**
     * Fetches the data from the API.
     * @param int $numberOfResults
     * @return array<GenericEntityWithRecipes>
     * @throws PortalApiServerException
     */
    private function fetchData(int $numberOfResults): array
    {
        $request = new ItemRandomRequest();
        $request->numberOfResults = $numberOfResults;
        $request->numberOfRecipesPerResult = $this->numberOfRecipesPerResult;

        try {
            /** @var ItemRandomResponse $response */
            $response = $this->apiClient->sendRequest($request)->wait();
            return $response->items;
        } catch (ClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }

    private function mapItem(GenericEntityWithRecipes $item): EntityData
    {
        return $this->mapperManager->map($item, new EntityData());
    }
}
