<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Helper;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\Mod;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Mod\ModListRequest;
use FactorioItemBrowser\Api\Client\Response\Mod\ModListResponse;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
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
    /**
     * The api client factory.
     * @var ApiClientFactory
     */
    protected $apiClientFactory;

    /**
     * The icons style fetcher.
     * @var IconsStyleFetcher
     */
    protected $iconsStyleFetcher;

    /**
     * The mapper manager.
     * @var MapperManagerInterface
     */
    protected $mapperManager;

    /**
     * Initializes the helper.
     * @param ApiClientFactory $apiClientFactory
     * @param IconsStyleFetcher $iconsStyleFetcher
     * @param MapperManagerInterface $mapperManager
     */
    public function __construct(
        ApiClientFactory $apiClientFactory,
        IconsStyleFetcher $iconsStyleFetcher,
        MapperManagerInterface $mapperManager
    ) {
        $this->apiClientFactory = $apiClientFactory;
        $this->iconsStyleFetcher = $iconsStyleFetcher;
        $this->mapperManager = $mapperManager;
    }

    /**
     * Create the setting meta data from the specified setting.
     * @param Setting $setting
     * @return SettingMetaData
     * @throws PortalApiServerException
     */
    public function createSettingMeta(Setting $setting): SettingMetaData
    {
        $settingData = new SettingMetaData();
        try {
            $this->mapperManager->map($setting, $settingData);
        } catch (MapperException $e) {
            throw new MappingException($e);
        }
        return $settingData;
    }

    /**
     * Creates the setting details data from the specified setting, requesting additionally needed data from the API.
     * @param Setting $setting
     * @return SettingDetailsData
     * @throws PortalApiServerException
     */
    public function createSettingDetails(Setting $setting): SettingDetailsData
    {
        $settingData = new SettingDetailsData();
        $apiClient = $this->apiClientFactory->create($setting);
        $modListRequest = new ModListRequest();

        try {
            $apiClient->sendRequest($modListRequest);
            $this->iconsStyleFetcher->request($setting, $this->extractModNames($setting));
            $this->mapperManager->map($setting, $settingData);

            /** @var ModListResponse $modListResponse */
            $modListResponse = $apiClient->fetchResponse($modListRequest);
            $settingData->setMods(array_map([$this, 'mapMod'], $modListResponse->getMods()))
                        ->setModIconsStyle($this->iconsStyleFetcher->process());
        } catch (ApiClientException $e) {
            throw new FailedApiRequestException($e);
        } catch (MapperException $e) {
            throw new MappingException($e);
        }
        return $settingData;
    }

    /**
     * Extracts the mod names from the setting.
     * @param Setting $setting
     * @return NamesByTypes
     */
    protected function extractModNames(Setting $setting): NamesByTypes
    {
        $namesByTypes = new NamesByTypes();
        foreach ($setting->getCombination()->getModNames() as $modName) {
            $namesByTypes->addValue(EntityType::MOD, $modName);
        }
        return $namesByTypes;
    }

    /**
     * Maps the mod its a transfer object.
     * @param Mod $mod
     * @return ModData
     * @throws PortalApiServerException
     */
    protected function mapMod(Mod $mod): ModData
    {
        $modData = new ModData();
        try {
            $this->mapperManager->map($mod, $modData);
        } catch (MapperException $e) {
            throw new MappingException($e);
        }
        return $modData;
    }
}
