<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler;

use BluePsyduck\MapperManager\MapperManagerInterface;
use DateTime;
use FactorioItemBrowser\PortalApi\Server\Constant\CombinationStatus;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
use FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\InitData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingData;
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
    private CombinationHelper $combinationHelper;
    private Setting $currentSetting;
    private MapperManagerInterface $mapperManager;
    private SidebarEntitiesHelper $sidebarEntitiesHelper;
    private string $scriptVersion;

    public function __construct(
        CombinationHelper $combinationHelper,
        Setting $currentSetting,
        MapperManagerInterface $mapperManager,
        SidebarEntitiesHelper $sidebarEntitiesHelper,
        string $scriptVersion
    ) {
        $this->combinationHelper = $combinationHelper;
        $this->currentSetting = $currentSetting;
        $this->mapperManager = $mapperManager;
        $this->sidebarEntitiesHelper = $sidebarEntitiesHelper;
        $this->scriptVersion = $scriptVersion;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws PortalApiServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->updateCombinationStatus($this->currentSetting->getCombination());
        $this->updateSetting($this->currentSetting);

        $response = new InitData();
        $response->scriptVersion = $this->scriptVersion;

        $response->setting = $this->mapperManager->map($this->currentSetting, new SettingData());
        if ($this->currentSetting->getIsTemporary()) {
            $lastUsedSetting = $this->currentSetting->getUser()->getLastUsedSetting();
            if ($lastUsedSetting !== null) {
                $response->lastUsedSetting = $this->mapperManager->map($lastUsedSetting, new SettingData());
            }
        }

        $response->sidebarEntities = array_map(
            [$this, 'mapSidebarEntity'],
            $this->currentSetting->getSidebarEntities()->toArray(),
        );

        return new TransferResponse($response);
    }

    /**
     * @param Combination $combination
     * @throws PortalApiServerException
     */
    private function updateCombinationStatus(Combination $combination): void
    {
        if ($this->isStatusUpdateNeeded($combination)) {
            $this->combinationHelper->updateStatus($combination);
        }
    }

    private function isStatusUpdateNeeded(Combination $combination): bool
    {
        $timeCut = new DateTime('-1 days');
        return $combination->getStatus() !== CombinationStatus::AVAILABLE
            || $combination->getLastCheckTime() === null
            || $combination->getLastCheckTime()->getTimestamp() < $timeCut->getTimestamp();
    }

    /**
     * @param Setting $setting
     * @throws PortalApiServerException
     */
    private function updateSetting(Setting $setting): void
    {
        $isAvailable = $setting->getCombination()->getStatus() === CombinationStatus::AVAILABLE;
        if ($isAvailable !== $setting->getHasData()) {
            $setting->setHasData($isAvailable);
            $this->sidebarEntitiesHelper->refreshLabels($setting);
        }
    }

    private function mapSidebarEntity(SidebarEntity $sidebarEntity): SidebarEntityData
    {
        return $this->mapperManager->map($sidebarEntity, new SidebarEntityData());
    }
}
