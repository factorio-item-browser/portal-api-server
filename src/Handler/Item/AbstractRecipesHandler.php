<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Item;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
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
    protected ClientInterface $apiClient;
    protected MapperManagerInterface $mapperManager;

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
        $type = $request->getAttribute('type', '');
        $name = $request->getAttribute('name', '');

        $queryParams = $request->getQueryParams();
        $indexOfFirstResult = (int) ($queryParams['indexOfFirstResult'] ?? 0);
        $numberOfResults = (int) ($queryParams['numberOfResults'] ?? 10);

        $item = $this->fetchData($type, $name, $indexOfFirstResult, $numberOfResults);
        if ($item === null) {
            throw new UnknownEntityException($type, $name);
        }

        $response = $this->mapperManager->map($item, new ItemRecipesData());
        return new TransferResponse($response);
    }

    /**
     * Fetches the data for the request.
     * @param string $type
     * @param string $name
     * @param int $indexOfFirstResult
     * @param int $numberOfResults
     * @return GenericEntityWithRecipes|null
     * @throws PortalApiServerException
     */
    abstract protected function fetchData(
        string $type,
        string $name,
        int $indexOfFirstResult,
        int $numberOfResults
    ): ?GenericEntityWithRecipes;
}
