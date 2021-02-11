<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Recipe;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Exception\NotFoundException;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeMachinesRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeMachinesResponse;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
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

        $queryParams = $request->getQueryParams();
        $indexOfFirstResult = (int) ($queryParams['indexOfFirstResult'] ?? 0);
        $numberOfResults = (int) ($queryParams['numberOfResults'] ?? 10);

        $machinesResponse = $this->fetchData($name, $indexOfFirstResult, $numberOfResults);
        $response = $this->mapperManager->map($machinesResponse, new RecipeMachinesData());
        return new TransferResponse($response);
    }

    /**
     * @param string $name
     * @param int $indexOfFirstResult
     * @param int $numberOfResults
     * @return RecipeMachinesResponse
     * @throws PortalApiServerException
     */
    private function fetchData(string $name, int $indexOfFirstResult, int $numberOfResults): RecipeMachinesResponse
    {
        $request = new RecipeMachinesRequest();
        $request->name = $name;
        $request->indexOfFirstResult = $indexOfFirstResult;
        $request->numberOfResults = $numberOfResults;

        try {
            /** @var RecipeMachinesResponse $response */
            $response = $this->apiClient->sendRequest($request)->wait();
            return $response;
        } catch (NotFoundException $e) {
            throw new UnknownEntityException(EntityType::RECIPE, $name);
        } catch (ClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }
}
