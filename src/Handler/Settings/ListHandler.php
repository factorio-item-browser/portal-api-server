<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Settings;

use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Helper\SettingHelper;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingsListData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The handler for requesting a list of available settings.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ListHandler implements RequestHandlerInterface
{
    private Setting $currentSetting;
    private User $currentUser;
    private SettingHelper $settingHelper;

    public function __construct(Setting $currentSetting, User $currentUser, SettingHelper $settingHelper)
    {
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
        $settings = array_map([$this->settingHelper, 'createSettingMeta'], $this->getFilteredSettings());
        $currentSetting = $this->settingHelper->createSettingDetails($this->currentSetting);

        $settingList = new SettingsListData();
        $settingList->settings = $settings;
        $settingList->currentSetting = $currentSetting;
        return new TransferResponse($settingList);
    }

    /**
     * Returns the settings of the current user, ignoring the temporary ones.
     * @return array<Setting>
     */
    protected function getFilteredSettings(): array
    {
        $result = [];
        foreach ($this->currentUser->getSettings() as $setting) {
            if (!$setting->getIsTemporary() || $setting->getId()->equals($this->currentSetting->getId())) {
                $result[] = $setting;
            }
        }
        return $result;
    }
}
