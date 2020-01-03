<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Recipe;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Exception\NotFoundException;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeMachinesRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeMachinesResponse;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeMachinesData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The handler for the recipe machines request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachinesHandler implements RequestHandlerInterface
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

        $queryParams = $request->getQueryParams();
        $indexOfFirstResult = (int) ($queryParams['indexOfFirstResult'] ?? 0);
        $numberOfResults = (int) ($queryParams['numberOfResults'] ?? 0);

        $machinesResponse = $this->fetchData($name, $indexOfFirstResult, $numberOfResults);
        $recipeMachinesData = $this->createRecipeMachinesData($machinesResponse);
        return new TransferResponse($recipeMachinesData);
    }

    /**
     * Fetches the data to the specified recipe name.
     * @param string $name
     * @param int $indexOfFirstResult
     * @param int $numberOfResults
     * @return RecipeMachinesResponse
     * @throws PortalApiServerException
     */
    protected function fetchData(string $name, int $indexOfFirstResult, int $numberOfResults): RecipeMachinesResponse
    {
        $request = new RecipeMachinesRequest();
        $request->setName($name)
                ->setIndexOfFirstResult($indexOfFirstResult)
                ->setNumberOfResults($numberOfResults);

        try {
            /** @var RecipeMachinesResponse $response */
            $response = $this->apiClient->fetchResponse($request);
            return $response;
        } catch (NotFoundException $e) {
            throw new UnknownEntityException(EntityType::RECIPE, $name);
        } catch (ApiClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }

    /**
     * Creates the recipe details data from the recipe.
     * @param RecipeMachinesResponse $machinesResponse
     * @return RecipeMachinesData
     * @throws PortalApiServerException
     */
    protected function createRecipeMachinesData(RecipeMachinesResponse $machinesResponse): RecipeMachinesData
    {
        $recipeMachinesData = new RecipeMachinesData();
        try {
            $this->mapperManager->map($machinesResponse, $recipeMachinesData);
        } catch (MapperException $e) {
            throw new MappingException($e);
        }
        return $recipeMachinesData;
    }
}
