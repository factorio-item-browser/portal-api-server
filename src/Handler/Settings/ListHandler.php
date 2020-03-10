<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Settings;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
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
class ListHandler extends AbstractSettingsHandler implements RequestHandlerInterface
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
        parent::__construct($mapperManager);

        $this->apiClient = $apiClient;
        $this->currentSetting = $currentSetting;
        $this->currentUser = $currentUser;
    }

    /**
     * Handles the request.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws PortalApiServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $currentMods = $this->fetchMods($this->apiClient);
        $settingDetails = $this->mapSettingDetails($this->currentSetting, $currentMods);

        $data = new SettingsListData();
        $data->setSettings(array_map([$this, 'mapSettingMeta'], $this->currentUser->getSettings()->toArray()))
             ->setCurrentSetting($settingDetails);

        return new TransferResponse($data);
    }
}
