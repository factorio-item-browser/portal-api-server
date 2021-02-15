<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Helper;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Request\Mod\ModListRequest;
use FactorioItemBrowser\Api\Client\Response\Mod\ModListResponse;
use FactorioItemBrowser\Api\Client\Transfer\Mod;
use FactorioItemBrowser\Common\Constant\Defaults;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Transfer\ModData;
use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;

/**
 * The helper for managing and mapping settings.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingHelper
{
    private ClientInterface $apiClient;
    private IconsStyleFetcher $iconsStyleFetcher;
    private MapperManagerInterface $mapperManager;

    public function __construct(
        ClientInterface $apiClient,
        IconsStyleFetcher $iconsStyleFetcher,
        MapperManagerInterface $mapperManager
    ) {
        $this->apiClient = $apiClient;
        $this->iconsStyleFetcher = $iconsStyleFetcher;
        $this->mapperManager = $mapperManager;
    }

    /**
     * Create the setting meta data from the specified setting.
     * @param Setting $setting
     * @return SettingMetaData
     */
    public function createSettingMeta(Setting $setting): SettingMetaData
    {
        return $this->mapperManager->map($setting, new SettingMetaData());
    }

    /**
     * Creates the setting details data from the specified setting, requesting additionally needed data from the API.
     * @param Setting $setting
     * @return SettingDetailsData
     * @throws PortalApiServerException
     */
    public function createSettingDetails(Setting $setting): SettingDetailsData
    {
        $modNames = new NamesByTypes();
        foreach ($setting->getCombination()->getModNames() as $modName) {
            $modNames->add(EntityType::MOD, $modName);
        }

        $modListRequest = new ModListRequest();
        if ($setting->getHasData()) {
            $modListRequest->combinationId = $setting->getCombination()->getId()->toString();
        } else {
            $modListRequest->combinationId = Defaults::COMBINATION_ID;
        }
        $modListRequest->locale = $setting->getLocale();
        $settingData = $this->mapperManager->map($setting, new SettingDetailsData());
        try {
            $iconsPromise = $this->iconsStyleFetcher->request($setting, $modNames);

            /** @var ModListResponse $modListResponse */
            $modListResponse = $this->apiClient->sendRequest($modListRequest)->wait();
            $settingData->mods = array_map([$this, 'mapMod'], $modListResponse->mods);
            $settingData->modIconsStyle = $this->iconsStyleFetcher->process($iconsPromise);

            $this->iconsStyleFetcher->addMissingEntities($settingData->modIconsStyle->processedEntities, $modNames);
        } catch (ClientException $e) {
            throw new FailedApiRequestException($e);
        }
        return $settingData;
    }

    private function mapMod(Mod $mod): ModData
    {
        return $this->mapperManager->map($mod, new ModData());
    }

    /**
     * Creates the setting details, without requesting the mod data. This is a temporary solution until the settings get
     * refactored when separating mod icons from their list.
     * @param Setting $setting
     * @return SettingDetailsData
     */
    public function createSettingDetailsWithoutMods(Setting $setting): SettingDetailsData
    {
        return $this->mapperManager->map($setting, new SettingDetailsData());
    }
}
