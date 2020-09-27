<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Settings;

use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\DeleteActiveSettingException;
use FactorioItemBrowser\PortalApi\Server\Exception\MissingSettingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
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
     * The setting repository.
     * @var SettingRepository
     */
    protected $settingRepository;

    /**
     * Initializes the handler.
     * @param Setting $currentSetting
     * @param User $currentUser
     * @param SettingRepository $settingRepository
     */
    public function __construct(
        Setting $currentSetting,
        User $currentUser,
        SettingRepository $settingRepository
    ) {
        $this->currentSetting = $currentSetting;
        $this->currentUser = $currentUser;
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
        $combinationId = Uuid::fromString($request->getAttribute('combination-id', ''));
        $setting = $this->currentUser->getSettingByCombinationId($combinationId);
        if ($setting === null) {
            throw new MissingSettingException($combinationId);
        }

        if ($this->currentSetting->getId()->equals($setting->getId())) {
            throw new DeleteActiveSettingException();
        }

        $this->settingRepository->deleteSetting($setting);

        return new EmptyResponse();
    }
}
