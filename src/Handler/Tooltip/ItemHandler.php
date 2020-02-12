<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Tooltip;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Exception\NotFoundException;
use FactorioItemBrowser\Api\Client\Request\Item\ItemProductRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemProductResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The handler for the item tooltips.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemHandler implements RequestHandlerInterface
{
    /**
     * The API client.
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
        $type = $request->getAttribute('type', '');
        $name = $request->getAttribute('name', '');

        $item = $this->fetchData($type, $name);
        $entityData = $this->createEntityData($item);
        return new TransferResponse($entityData);
    }

    /**
     * Fetches the data for the tooltip.
     * @param string $type
     * @param string $name
     * @return GenericEntityWithRecipes
     * @throws PortalApiServerException
     */
    protected function fetchData(string $type, string $name): GenericEntityWithRecipes
    {
        $request = new ItemProductRequest();
        $request->setType($type)
                ->setName($name)
                ->setIndexOfFirstResult(0)
                ->setNumberOfResults($this->numberOfRecipesPerResult);

        try {
            /** @var ItemProductResponse $response */
            $response = $this->apiClient->fetchResponse($request);
            return $response->getItem();
        } catch (NotFoundException $e) {
            throw new UnknownEntityException($type, $name);
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
