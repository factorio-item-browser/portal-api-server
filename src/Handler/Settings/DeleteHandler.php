<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Settings;

use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\DeleteActiveSettingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Helper\SettingHelper;
use FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

/**
 * The handler for deleting a setting.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DeleteHandler implements RequestHandlerInterface
{
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
     * The setting repository.
     * @var SettingRepository
     */
    protected $settingRepository;

    /**
     * Initializes the handler.
     * @param User $currentUser
     * @param SettingHelper $settingHelper
     * @param SettingRepository $settingRepository
     */
    public function __construct(User $currentUser, SettingHelper $settingHelper, SettingRepository $settingRepository)
    {
        $this->currentUser = $currentUser;
        $this->settingHelper = $settingHelper;
        $this->settingRepository = $settingRepository;
    }

    /**
     * Handles the request.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws PortalApiServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $settingId = $request->getAttribute('setting-id', '');
        $setting = $this->settingHelper->findInCurrentUser(Uuid::fromString($settingId));

        if (
            $this->currentUser->getCurrentSetting() !== null
            && $this->currentUser->getCurrentSetting()->getId()->compareTo($setting->getId()) === 0
        ) {
            throw new DeleteActiveSettingException();
        }

        $this->settingRepository->deleteSetting($setting);

        return new EmptyResponse();
    }
}
