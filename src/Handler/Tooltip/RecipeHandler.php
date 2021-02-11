<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Tooltip;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Transfer\Recipe;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeDetailsResponse;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
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
    private ClientInterface $apiClient;
    private MapperManagerInterface $mapperManager;

    public function __construct(
        ClientInterface $apiClient,
        MapperManagerInterface $mapperManager
    ) {
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
        $name = $request->getAttribute('name', '');

        $recipe = $this->fetchData($name);
        $response = $this->mapperManager->map($recipe, new EntityData());
        return new TransferResponse($response);
    }

    /**
     * @param string $name
     * @return Recipe
     * @throws PortalApiServerException
     */
    protected function fetchData(string $name): Recipe
    {
        $request = new RecipeDetailsRequest();
        $request->names = [$name];

        try {
            /** @var RecipeDetailsResponse $response */
            $response = $this->apiClient->sendRequest($request)->wait();
            foreach ($response->recipes as $recipe) {
                if ($recipe->name === $name) {
                    return $recipe;
                }
            }

            throw new UnknownEntityException(EntityType::RECIPE, $name);
        } catch (ClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }
}
