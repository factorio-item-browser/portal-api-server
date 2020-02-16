<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Item\ItemRandomRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemRandomResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
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
        $numberOfResults = (int) ($queryParams['numberOfResults'] ?? 0);

        $items = $this->fetchData($numberOfResults);
        $entityData = array_map([$this, 'createEntityData'], $items);
        return new TransferResponse($entityData);
    }

    /**
     * Fetches the data from the API.
     * @param int $numberOfResults
     * @return array|GenericEntityWithRecipes[]
     * @throws PortalApiServerException
     */
    protected function fetchData(int $numberOfResults): array
    {
        $request = new ItemRandomRequest();
        $request->setNumberOfResults($numberOfResults)
                ->setNumberOfRecipesPerResult($this->numberOfRecipesPerResult);

        try {
            /** @var ItemRandomResponse $response */
            $response = $this->apiClient->fetchResponse($request);
            return $response->getItems();
        } catch (ApiClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }

    /**
     * Creates the entity data transfer from the entity.
     * @param GenericEntityWithRecipes $item
     * @return EntityData
     * @throws PortalApiServerException
     */
    protected function createEntityData(GenericEntityWithRecipes $item): EntityData
    {
        $entityData = new EntityData();
        try {
            $this->mapperManager->map($item, $entityData);
        } catch (MapperException $e) {
            throw new MappingException($e);
        }
        return $entityData;
    }
}
