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
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\ModData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;
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
    /**
     * The API client.
     * @var ApiClientInterface
     */
    protected $apiClient;

    /**
     * The current setting.
     * @var Setting
     */
    protected $currentSetting;

    /**
     * The current user.
     * @var User
     */
    protected $currentUser;

    /**
     * The mapper manager.
     * @var MapperManagerInterface
     */
    protected $mapperManager;

    /**
     * Initializes the handler.
     * @param ApiClientInterface $apiClient
     * @param Setting $currentSetting
     * @param User $currentUser
     * @param MapperManagerInterface $mapperManager
     */
    public function __construct(
        ApiClientInterface $apiClient,
        Setting $currentSetting,
        User $currentUser,
        MapperManagerInterface $mapperManager
    ) {
        $this->apiClient = $apiClient;
        $this->currentSetting = $currentSetting;
        $this->currentUser = $currentUser;
        $this->mapperManager = $mapperManager;
    }

    /**
     * Handles the request.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws PortalApiServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = new SettingsListData();
        $data->setSettings(array_map([$this, 'mapSettingMeta'], $this->currentUser->getSettings()->toArray()))
             ->setCurrentSetting($this->createSettingDetails());

        return new TransferResponse($data);
    }

    /**
     * Creates the setting details of the currently active setting.
     * @return SettingDetailsData
     * @throws PortalApiServerException
     */
    protected function createSettingDetails(): SettingDetailsData
    {
        $settingData = new SettingDetailsData();
        try {
            $this->mapperManager->map($this->currentSetting, $settingData);
        } catch (MapperException $e) {
            throw new MappingException($e);
        }

        $settingData->setMods(array_map([$this, 'mapMod'], $this->fetchMods()));
        return $settingData;
    }

    /**
     * Fetches the mods of the current setting.
     * @return array|Mod[]
     * @throws PortalApiServerException
     */
    protected function fetchMods(): array
    {
        try {
            $request = new ModListRequest();
            /** @var ModListResponse $response */
            $response = $this->apiClient->fetchResponse($request);
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
