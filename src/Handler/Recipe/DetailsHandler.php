<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Recipe;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Exception\NotFoundException;
use FactorioItemBrowser\Api\Client\Transfer\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeDetailsResponse;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
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
    private ClientInterface $apiClient;
    private MapperManagerInterface $mapperManager;

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
        $name = $request->getAttribute('name', '');

        $recipe = $this->fetchData($name);
        if ($recipe === null) {
            throw new UnknownEntityException(EntityType::RECIPE, $name);
        }

        $response = $this->mapperManager->map($recipe, new RecipeDetailsData());
        return new TransferResponse($response);
    }

    /**
     * @param string $name
     * @return RecipeWithExpensiveVersion|null
     * @throws PortalApiServerException
     */
    private function fetchData(string $name): ?RecipeWithExpensiveVersion
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
            return null;
        } catch (NotFoundException $e) {
            return null;
        } catch (ClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }
}
