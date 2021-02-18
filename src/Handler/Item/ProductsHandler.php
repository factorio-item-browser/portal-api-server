<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Item;

use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Exception\NotFoundException;
use FactorioItemBrowser\Api\Client\Request\Item\ItemProductRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemProductResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;

/**
 * The handler for fetching the products recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ProductsHandler extends AbstractRecipesHandler
{
    protected function fetchData(
        string $type,
        string $name,
        int $indexOfFirstResult,
        int $numberOfResults
    ): ?GenericEntityWithRecipes {
        $request = new ItemProductRequest();
        $request->type = $type;
        $request->name = $name;
        $request->indexOfFirstResult = $indexOfFirstResult;
        $request->numberOfResults = $numberOfResults;

        try {
            /** @var ItemProductResponse $response */
            $response = $this->apiClient->sendRequest($request)->wait();
            return $response->item;
        } catch (NotFoundException $e) {
            return null;
        } catch (ClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }
}
