<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Session;

use Exception;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Combination\CombinationStatusRequest;
use FactorioItemBrowser\Api\Client\Response\Combination\CombinationStatusResponse;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
use FactorioItemBrowser\PortalApi\Server\Constant\CombinationStatus;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
use FactorioItemBrowser\PortalApi\Server\Helper\SettingHelper;
use FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SessionInitData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The handler for initializing the session.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class InitHandler implements RequestHandlerInterface
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
     * The current user setting.
     * @var Setting
     */
    protected $currentSetting;

    /**
     * The setting helper.
     * @var SettingHelper
     */
    protected $settingHelper;

    /**
     * The sidebar entities helper.
     * @var SidebarEntitiesHelper
     */
    protected $sidebarEntitiesHelper;

    /**
     * Initializes the handler.
     * @param ApiClientFactory $apiClientFactory
     * @param CombinationHelper $combinationHelper
     * @param Setting $currentSetting
     * @param SettingHelper $settingHelper
     * @param SidebarEntitiesHelper $sidebarEntitiesHelper
     */
    public function __construct(
        ApiClientFactory $apiClientFactory,
        CombinationHelper $combinationHelper,
        Setting $currentSetting,
        SettingHelper $settingHelper,
        SidebarEntitiesHelper $sidebarEntitiesHelper
    ) {
        $this->apiClientFactory = $apiClientFactory;
        $this->combinationHelper = $combinationHelper;
        $this->currentSetting = $currentSetting;
        $this->settingHelper = $settingHelper;
        $this->sidebarEntitiesHelper = $sidebarEntitiesHelper;
    }

    /**
     * Handles the request.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->updateCombinationStatus();
        $this->updateSetting();

        $data = new SessionInitData();
        $data->setSetting($this->settingHelper->createSettingMeta($this->currentSetting))
             ->setSettingHash($this->settingHelper->calculateHash($this->currentSetting))
             ->setLocale($this->currentSetting->getLocale())
             ->setSidebarEntities($this->getCurrentSidebarEntities());

        return new TransferResponse($data);
    }

    /**
     * Updates the status of the currently loaded combination.
     * @throws Exception
     */
    protected function updateCombinationStatus(): void
    {
        try {
            $apiClient = $this->apiClientFactory->createWithoutFallback($this->currentSetting);
            $request = new CombinationStatusRequest();

            /** @var CombinationStatusResponse $response */
            $response = $apiClient->fetchResponse($request);
            $this->combinationHelper->hydrateStatusResponseToCombination(
                $response,
                $this->currentSetting->getCombination()
            );
        } catch (ApiClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }

    /**
     * Updates the setting, if needed.
     * @throws Exception
     */
    protected function updateSetting(): void
    {
        $isAvailable = $this->currentSetting->getCombination()->getStatus() === CombinationStatus::AVAILABLE;
        if ($isAvailable !== $this->currentSetting->getHasData()) {
            $this->currentSetting->setHasData($isAvailable)
                                 ->setApiAuthorizationToken('');

            $apiClient = $this->apiClientFactory->create($this->currentSetting);
            $apiClient->clearAuthorizationToken();

            $this->sidebarEntitiesHelper->refreshLabels($this->currentSetting);
        }
    }

    /**
     * Returns the current sidebar entities.
     * @return array<SidebarEntityData>
     * @throws PortalApiServerException
     */
    protected function getCurrentSidebarEntities(): array
    {
        return $this->sidebarEntitiesHelper->mapEntities($this->currentSetting->getSidebarEntities()->toArray());
    }
}
