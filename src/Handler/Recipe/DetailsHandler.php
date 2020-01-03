<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Recipe;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeDetailsResponse;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeDetailsData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The handler of the recipe details request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DetailsHandler implements RequestHandlerInterface
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
        $name = $request->getAttribute('name', '');

        $recipe = $this->fetchData($name);
        if ($recipe === null) {
            throw new UnknownEntityException(EntityType::RECIPE, $name);
        }

        $recipeDetailsData = $this->createRecipeDetailsData($recipe);
        return new TransferResponse($recipeDetailsData);
    }

    /**
     * Fetches the data to the specified recipe name.
     * @param string $name
     * @return RecipeWithExpensiveVersion|null
     * @throws PortalApiServerException
     */
    protected function fetchData(string $name): ?RecipeWithExpensiveVersion
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
            return null;
        } catch (ApiClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }

    /**
     * Creates the recipe details data from the recipe.
     * @param RecipeWithExpensiveVersion $recipe
     * @return RecipeDetailsData
     * @throws PortalApiServerException
     */
    protected function createRecipeDetailsData(RecipeWithExpensiveVersion $recipe): RecipeDetailsData
    {
        $recipeDetailsData = new RecipeDetailsData();
        try {
            $this->mapperManager->map($recipe, $recipeDetailsData);
        } catch (MapperException $e) {
            throw new MappingException($e);
        }
        return $recipeDetailsData;
    }
}
