<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Item;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemRecipesData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The abstract class of the recipes handler.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractRecipesHandler implements RequestHandlerInterface
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
        $type = $request->getAttribute('type', '');
        $name = $request->getAttribute('name', '');

        $queryParams = $request->getQueryParams();
        $indexOfFirstResult = (int) ($queryParams['indexOfFirstResult'] ?? 0);
        $numberOfResults = (int) ($queryParams['numberOfResults'] ?? 0);

        $item = $this->fetchData($type, $name, $indexOfFirstResult, $numberOfResults);
        if ($item === null) {
            throw new UnknownEntityException($type, $name);
        }

        $itemRecipesData = $this->createItemRecipesData($item);
        return new TransferResponse($itemRecipesData);
    }

    /**
     * Fetches the data for the request.
     * @param string $type
     * @param string $name
     * @param int $indexOfFirstResult
     * @param int $numberOfResults
     * @return GenericEntityWithRecipes|null
     */
    abstract protected function fetchData(
        string $type,
        string $name,
        int $indexOfFirstResult,
        int $numberOfResults
    ): ?GenericEntityWithRecipes;

    /**
     * Creates the item recipes data from the item.
     * @param GenericEntityWithRecipes $item
     * @return ItemRecipesData
     * @throws PortalApiServerException
     */
    protected function createItemRecipesData(GenericEntityWithRecipes $item): ItemRecipesData
    {
        $itemRecipesData = new ItemRecipesData();
        try {
            $this->mapperManager->map($item, $itemRecipesData);
        } catch (MapperException $e) {
            throw new MappingException($e);
        }
        return $itemRecipesData;
    }
}
