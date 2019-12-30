<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Item;

use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Exception\NotFoundException;
use FactorioItemBrowser\Api\Client\Request\Item\ItemIngredientRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemIngredientResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;

/**
 * The handler for fetching the ingredients recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IngredientsHandler extends AbstractRecipesHandler
{
    /**
     * Fetches the data for the request.
     * @param string $type
     * @param string $name
     * @param int $indexOfFirstResult
     * @param int $numberOfResults
     * @return GenericEntityWithRecipes|null
     * @throws FailedApiRequestException
     */
    protected function fetchData(
        string $type,
        string $name,
        int $indexOfFirstResult,
        int $numberOfResults
    ): ?GenericEntityWithRecipes {
        $apiRequest = new ItemIngredientRequest();
        $apiRequest->setType($type)
                   ->setName($name)
                   ->setIndexOfFirstResult($indexOfFirstResult)
                   ->setNumberOfResults($numberOfResults);

        try {
            /** @var ItemIngredientResponse $response */
            $response = $this->apiClient->fetchResponse($apiRequest);
            return $response->getItem();
        } catch (NotFoundException $e) {
            return null;
        } catch (ApiClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }
}
