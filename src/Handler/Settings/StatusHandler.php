<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Settings;

use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
use FactorioItemBrowser\PortalApi\Server\Helper\SettingHelper;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingStatusData;
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
    private CombinationHelper $combinationHelper;
    private Setting $currentSetting;
    private User $currentUser;
    private SettingHelper $settingHelper;

    public function __construct(
        CombinationHelper $combinationHelper,
        Setting $currentSetting,
        User $currentUser,
        SettingHelper $settingHelper
    ) {
        $this->combinationHelper = $combinationHelper;
        $this->currentSetting = $currentSetting;
        $this->currentUser = $currentUser;
        $this->settingHelper = $settingHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws PortalApiServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $modNames = $request->getParsedBody();
        if (is_array($modNames)) {
            $combination = $this->combinationHelper->createForModNames($modNames);
        } else {
            $combination = $this->currentSetting->getCombination();
            $this->combinationHelper->updateStatus($combination);
        }

        $response = new SettingStatusData();
        $response->status = $combination->getStatus();
        $response->exportTime = $combination->getExportTime();

        $existingSetting = $this->currentUser->getSettingByCombinationId($combination->getId());
        if ($existingSetting !== null) {
            $response->existingSetting = $this->settingHelper->createSettingDetailsWithoutMods($existingSetting);
        }
        return new TransferResponse($response);
    }
}
