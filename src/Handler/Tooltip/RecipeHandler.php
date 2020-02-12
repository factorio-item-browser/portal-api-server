<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Tooltip;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\Recipe;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeDetailsResponse;
use FactorioItemBrowser\Common\Constant\EntityType;
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
 * The handler for the recipe tooltips.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeHandler implements RequestHandlerInterface
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
        $name = $request->getAttribute('name', '');

        $recipe = $this->fetchData($name);
        $entityData = $this->createEntityData($recipe);
        return new TransferResponse($entityData);
    }

    /**
     * Fetches the data for the tooltip.
     * @param string $name
     * @return Recipe
     * @throws PortalApiServerException
     */
    protected function fetchData(string $name): Recipe
    {
        $request = new RecipeDetailsRequest();
        $request->setNames([$name]);

        try {
            /** @var RecipeDetailsResponse $response */
            $response = $this->apiClient->fetchResponse($request);
            foreach ($response->getRecipes() as $recipe) {
                if ($recipe->getName() === $name) {
                    return $recipe;
                }
            }

            throw new UnknownEntityException(EntityType::RECIPE, $name);
        } catch (ApiClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }

    /**
     * Creates the entity data transfer from the recipe.
     * @param Recipe $recipe
     * @return EntityData
     * @throws PortalApiServerException
     */
    protected function createEntityData(Recipe $recipe): EntityData
    {
        $entityData = new EntityData();
        try {
            $this->mapperManager->map($recipe, $entityData);
        } catch (MapperException $e) {
            throw new MappingException($e);
        }
        return $entityData;
    }
}
