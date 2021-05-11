<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Setting;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingData;
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
    private MapperManagerInterface $mapperManager;

    public function __construct(Setting $currentSetting, User $currentUser, MapperManagerInterface $mapperManager)
    {
        $this->currentSetting = $currentSetting;
        $this->currentUser = $currentUser;
        $this->mapperManager = $mapperManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $settings = array_map([$this, 'mapSetting'], $this->getFilteredSettings());
        return new TransferResponse($settings);
    }

    /**
     * Returns the settings of the current user, ignoring the temporary ones.
     * @return array<Setting>
     */
    private function getFilteredSettings(): array
    {
        $settings = [];
        foreach ($this->currentUser->getSettings() as $setting) {
            if (!$setting->getIsTemporary() || $setting->getId()->equals($this->currentSetting->getId())) {
                $settings[] = $setting;
            }
        }
        return $settings;
    }

    private function mapSetting(Setting $setting): SettingData
    {
        return $this->mapperManager->map($setting, new SettingData());
    }
}
