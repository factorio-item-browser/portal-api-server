<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Settings;

use Exception;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Combination\CombinationStatusRequest;
use FactorioItemBrowser\Api\Client\Response\Combination\CombinationStatusResponse;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\InvalidRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingStatusData;
use JMS\Serializer\SerializerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The handler of the status request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class StatusHandler implements RequestHandlerInterface
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
     * The current setting.
     * @var Setting
     */
    protected $currentSetting;

    /**
     * The serializer.
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Initializes the handler.
     * @param ApiClientFactory $apiClientFactory
     * @param CombinationHelper $combinationHelper
     * @param Setting $currentSetting
     * @param SerializerInterface $portalApiServerSerializer
     */
    public function __construct(
        ApiClientFactory $apiClientFactory,
        CombinationHelper $combinationHelper,
        Setting $currentSetting,
        SerializerInterface $portalApiServerSerializer
    ) {
        $this->apiClientFactory = $apiClientFactory;
        $this->combinationHelper = $combinationHelper;
        $this->currentSetting = $currentSetting;
        $this->serializer = $portalApiServerSerializer;
    }

    /**
     * Handles the request.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $apiClient = $this->getApiClientForRequest($request);
        $combinationStatus = $this->requestCombinationStatus($apiClient);
        $combination = $this->combinationHelper->createCombinationFromStatusResponse($combinationStatus);
        $settingStatus = $this->createSettingStatus($combination);
        return new TransferResponse($settingStatus);
    }

    /**
     * Returns the API client to use for the specified request.
     * @param ServerRequestInterface $request
     * @return ApiClientInterface
     * @throws PortalApiServerException
     */
    protected function getApiClientForRequest(ServerRequestInterface $request): ApiClientInterface
    {
        if ($request->getMethod() === 'POST') {
            $modNames = $this->extractModNamesFromRequest($request);
            return $this->apiClientFactory->createForModNames($modNames);
        }

        return $this->apiClientFactory->create($this->currentSetting);
    }

    /**
     * Extracts the mod names from the specified request.
     * @param ServerRequestInterface $request
     * @return array<string>
     * @throws PortalApiServerException
     */
    protected function extractModNamesFromRequest(ServerRequestInterface $request): array
    {
        try {
            /** @var class-string<mixed> $type */
            $type = 'array<string>';
            $modNames = $this->serializer->deserialize($request->getBody()->getContents(), $type, 'json');
        } catch (Exception $e) {
            throw new InvalidRequestException($e->getMessage(), $e);
        }

        if (count($modNames) === 0) {
            throw new InvalidRequestException('Missing mod names in request.');
        }

        return $modNames;
    }

    /**
     * Requests the status of the combination, using the provided pre-configured API client.
     * @param ApiClientInterface $apiClient
     * @return CombinationStatusResponse
     * @throws FailedApiRequestException
     */
    protected function requestCombinationStatus(ApiClientInterface $apiClient): CombinationStatusResponse
    {
        $request = new CombinationStatusRequest();
        try {
            /** @var CombinationStatusResponse $response */
            $response = $apiClient->fetchResponse($request);
            return $response;
        } catch (ApiClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }

    /**
     * Creates the setting status from the combination.
     * @param Combination $combination
     * @return SettingStatusData
     */
    protected function createSettingStatus(Combination $combination): SettingStatusData
    {
        $settingStatus = new SettingStatusData();
        $settingStatus->setStatus($combination->getStatus())
                      ->setExportTime($combination->getExportTime());
        return $settingStatus;
    }
}
