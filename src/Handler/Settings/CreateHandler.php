<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Settings;

use Exception;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Combination\CombinationExportRequest;
use FactorioItemBrowser\Api\Client\Request\Combination\CombinationStatusRequest;
use FactorioItemBrowser\Api\Client\Response\Combination\CombinationStatusResponse;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
use FactorioItemBrowser\PortalApi\Server\Constant\CombinationStatus;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\InvalidRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
use FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingCreateData;
use JMS\Serializer\SerializerInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The handler for creating a new setting.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CreateHandler implements RequestHandlerInterface
{
    /**
     * The api client factory.
     * @var ApiClientFactory
     */
    protected $apiClientFactory;

    /**
     * The combination helper.
     * @var CombinationHelper
     */
    protected $combinationHelper;

    /**
     * The current user.
     * @var User
     */
    protected $currentUser;

    /**
     * The serializer.
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * The setting repository.
     * @var SettingRepository
     */
    protected $settingRepository;

    /**
     * Initializes the handler.
     * @param ApiClientFactory $apiClientFactory
     * @param CombinationHelper $combinationHelper
     * @param User $currentUser
     * @param SerializerInterface $portalApiServerSerializer
     * @param SettingRepository $settingRepository
     */
    public function __construct(
        ApiClientFactory $apiClientFactory,
        CombinationHelper $combinationHelper,
        User $currentUser,
        SerializerInterface $portalApiServerSerializer,
        SettingRepository $settingRepository
    ) {
        $this->apiClientFactory = $apiClientFactory;
        $this->combinationHelper = $combinationHelper;
        $this->currentUser = $currentUser;
        $this->serializer = $portalApiServerSerializer;
        $this->settingRepository = $settingRepository;
    }

    /**
     * Handles the request.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $settingData = $this->parseRequestBody($request);
        $apiClient = $this->apiClientFactory->createForModNames($settingData->getModNames());
        $combinationStatus = $this->fetchCombinationStatus($apiClient);
        $combination = $this->fetchCombination($combinationStatus);

        $this->triggerExport($combination, $apiClient);
        $this->createSetting($combination, $settingData);

        return new EmptyResponse();
    }

    /**
     * Parses the body of the request.
     * @param ServerRequestInterface $request
     * @return SettingCreateData
     * @throws PortalApiServerException
     */
    protected function parseRequestBody(ServerRequestInterface $request): SettingCreateData
    {
        try {
            $requestBody = $request->getBody()->getContents();
            return $this->serializer->deserialize($requestBody, SettingCreateData::class, 'json');
        } catch (Exception $e) {
            throw new InvalidRequestException($e->getMessage(), $e);
        }
    }

    /**
     * Fetches the current combination status for the specified mod names.
     * @param ApiClientInterface $apiClient
     * @return CombinationStatusResponse
     * @throws PortalApiServerException
     */
    protected function fetchCombinationStatus(ApiClientInterface $apiClient): CombinationStatusResponse
    {
        try {
            /** @var CombinationStatusResponse $response */
            $response = $apiClient->fetchResponse(new CombinationStatusRequest());
            return $response;
        } catch (ApiClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }

    /**
     * Fetches the combination to the status, creating it if necessary.
     * @param CombinationStatusResponse $combinationStatus
     * @return Combination
     * @throws Exception
     */
    protected function fetchCombination(CombinationStatusResponse $combinationStatus): Combination
    {
        $combination = $this->combinationHelper->createCombinationFromStatusResponse($combinationStatus);
        $this->combinationHelper->persist($combination);
        return $combination;
    }

    /**
     * Triggers an export of the combination, if one is needed.
     * @param Combination $combination
     * @param ApiClientInterface $apiClient
     * @throws Exception
     */
    protected function triggerExport(Combination $combination, ApiClientInterface $apiClient): void
    {
        if ($combination->getStatus() === CombinationStatus::UNKNOWN) {
            try {
                /** @var CombinationStatusResponse $response */
                $response = $apiClient->fetchResponse(new CombinationExportRequest());
            } catch (ApiClientException $e) {
                throw new FailedApiRequestException($e);
            }
            $this->combinationHelper->hydrateStatusResponseToCombination($response, $combination);
        }
    }

    /**
     * Creates a new setting with the specified combination and data, persisting it to the database.
     * @param Combination $combination
     * @param SettingCreateData $settingData
     * @throws Exception
     */
    protected function createSetting(Combination $combination, SettingCreateData $settingData): void
    {
        $setting = $this->settingRepository->createSetting($this->currentUser, $combination);
        $setting->setName($settingData->getName())
                ->setRecipeMode($settingData->getRecipeMode())
                ->setLocale($settingData->getLocale());
        $this->currentUser->setCurrentSetting($setting);
    }
}
