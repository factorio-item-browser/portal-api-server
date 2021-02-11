<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Tooltip;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Exception\NotFoundException;
use FactorioItemBrowser\Api\Client\Request\Item\ItemProductRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemProductResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
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
        $type = $request->getAttribute('type', '');
        $name = $request->getAttribute('name', '');

        $item = $this->fetchData($type, $name);

        $response = $this->mapperManager->map($item, new EntityData());
        return new TransferResponse($response);
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
        $request->type = $type;
        $request->name = $name;
        $request->indexOfFirstResult = 0;
        $request->numberOfResults = $this->numberOfRecipesPerResult;

        try {
            /** @var ItemProductResponse $response */
            $response = $this->apiClient->sendRequest($request)->wait();
            return $response->item;
        } catch (NotFoundException $e) {
            throw new UnknownEntityException($type, $name);
        } catch (ClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }
}
