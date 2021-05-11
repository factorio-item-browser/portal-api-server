<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Setting;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Request\Mod\ModListRequest;
use FactorioItemBrowser\Api\Client\Response\Mod\ModListResponse;
use FactorioItemBrowser\CombinationApi\Client\Response\Combination\ValidateResponse;
use FactorioItemBrowser\Common\Constant\Defaults;
use FactorioItemBrowser\PortalApi\Server\Constant\CombinationStatus;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingValidationData;
use FactorioItemBrowser\PortalApi\Server\Transfer\ValidationProblemData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The handler for validating a setting.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ValidateHandler implements RequestHandlerInterface
{
    private ClientInterface $apiClient;
    private CombinationHelper $combinationHelper;
    private User $currentUser;
    private MapperManagerInterface $mapperManager;

    public function __construct(
        ClientInterface $apiClient,
        CombinationHelper $combinationHelper,
        User $currentUser,
        MapperManagerInterface $mapperManager
    ) {
        $this->apiClient = $apiClient;
        $this->combinationHelper = $combinationHelper;
        $this->currentUser = $currentUser;
        $this->mapperManager = $mapperManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws PortalApiServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var array<string> $modNames */
        $modNames = $request->getParsedBody();

        $combination = $this->combinationHelper->createForModNames($modNames);

        $response = new SettingValidationData();
        $response->combinationId = $combination->getId()->toString();
        $response->status = $combination->getStatus();

        if ($combination->getStatus() === CombinationStatus::UNKNOWN) {
            $validationResponse = $this->combinationHelper->validate($combination, $this->fetchBaseVersion());

            $response->isValid = $validationResponse->isValid;
            $response->validationProblems = $this->mapValidationProblems($validationResponse);
        } else {
            // We added a job at some point, so the combination must be valid.
            $response->isValid = true;
        }

        $existingSetting = $this->currentUser->getSettingByCombinationId($combination->getId());
        if ($existingSetting !== null) {
            $response->existingSetting = $this->mapperManager->map($existingSetting, new SettingData());
        }

        return new TransferResponse($response);
    }

    /**
     * @return string
     * @throws FailedApiRequestException
     */
    private function fetchBaseVersion(): string
    {
        $request = new ModListRequest();
        $request->combinationId = Defaults::COMBINATION_ID;

        try {
            /** @var ModListResponse $response */
            $response = $this->apiClient->sendRequest($request)->wait();
            return $response->mods[0]->version;
        } catch (ClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }

    /**
     * @param ValidateResponse $validateResponse
     * @return array<ValidationProblemData>
     */
    private function mapValidationProblems(ValidateResponse $validateResponse): array
    {
        $problems = [];
        foreach ($validateResponse->mods as $mod) {
            foreach ($mod->problems as $problem) {
                $problemData = new ValidationProblemData();
                $problemData->mod = $mod->name;
                $problemData->version = $mod->version;
                $problemData->type = $problem->type;
                $problemData->dependency = $problem->dependency;
                $problems[] = $problemData;
            }
        }
        return $problems;
    }
}
