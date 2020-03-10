<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Settings;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\Mod;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Mod\ModListRequest;
use FactorioItemBrowser\Api\Client\Response\Mod\ModListResponse;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Transfer\ModData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;

/**
 * The abstract class of the settings handler.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractSettingsHandler
{
    /**
     * The mapper manager.
     * @var MapperManagerInterface
     */
    protected $mapperManager;

    /**
     * Initializes the handler.
     * @param MapperManagerInterface $mapperManager
     */
    public function __construct(MapperManagerInterface $mapperManager)
    {
        $this->mapperManager = $mapperManager;
    }

    /**
     * Fetches the mods of the current setting.
     * @param ApiClientInterface $apiClient
     * @return array|Mod[]
     * @throws FailedApiRequestException
     */
    protected function fetchMods(ApiClientInterface $apiClient): array
    {
        try {
            $request = new ModListRequest();

            /** @var ModListResponse $response */
            $response = $apiClient->fetchResponse($request);

            return $response->getMods();
        } catch (ApiClientException $e) {
            throw new FailedApiRequestException($e);
        }
    }

    /**
     * Maps the meta data of the setting.
     * @param Setting $setting
     * @return SettingMetaData
     * @throws PortalApiServerException
     */
    protected function mapSettingMeta(Setting $setting): SettingMetaData
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
     * Creates the setting details of the currently active setting.
     * @param Setting $setting
     * @param array|Mod[] $mods
     * @return SettingDetailsData
     * @throws MappingException
     */
    protected function mapSettingDetails(Setting $setting, array $mods): SettingDetailsData
    {
        $settingData = new SettingDetailsData();
        try {
            $this->mapperManager->map($setting, $settingData);
        } catch (MapperException $e) {
            throw new MappingException($e);
        }

        $settingData->setMods(array_map([$this, 'mapMod'], $mods));
        return $settingData;
    }

    /**
     * Maps the mod.
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
