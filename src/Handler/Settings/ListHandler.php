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
     * The setting helper.
     * @var SettingHelper
     */
    protected $settingHelper;

    /**
     * Initializes the handler.
     * @param Setting $currentSetting
     * @param User $currentUser
     * @param SettingHelper $settingHelper
     */
    public function __construct(Setting $currentSetting, User $currentUser, SettingHelper $settingHelper)
    {
        $this->currentSetting = $currentSetting;
        $this->currentUser = $currentUser;
        $this->settingHelper = $settingHelper;
    }

    /**
     * Handles the request.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws PortalApiServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $settings = array_map(
            [$this->settingHelper, 'createSettingMeta'],
            $this->currentUser->getSettings()->toArray()
        );
        $currentSetting = $this->settingHelper->createSettingDetails($this->currentSetting);

        $settingList = new SettingsListData();
        $settingList->setSettings($settings)
                    ->setCurrentSetting($currentSetting);
        return new TransferResponse($settingList);
    }
}
