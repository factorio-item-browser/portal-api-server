<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Settings;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * The handler for requesting the details to a setting.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DetailsHandler extends AbstractSettingsHandler implements RequestHandlerInterface
{
    /**
     * The api client factory.
     * @var ApiClientFactory
     */
    protected $apiClientFactory;

    /**
     * The current user.
     * @var User
     */
    protected $currentUser;

    /**
     * Initializes the handler.
     * @param ApiClientFactory $apiClientFactory
     * @param User $currentUser
     * @param MapperManagerInterface $mapperManager
     */
    public function __construct(
        ApiClientFactory $apiClientFactory,
        User $currentUser,
        MapperManagerInterface $mapperManager
    ) {
        parent::__construct($mapperManager);

        $this->apiClientFactory = $apiClientFactory;
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
        $settingId = $request->getAttribute('setting-id', '');
        $setting = $this->findSetting(Uuid::fromString($settingId));

        $apiClient = $this->apiClientFactory->create($setting);
        $mods = $this->fetchMods($apiClient);
        $settingDetails = $this->mapSettingDetails($setting, $mods);

        return new TransferResponse($settingDetails);
    }

    /**
     * Finds the setting with the specified id.
     * @param UuidInterface $settingId
     * @return Setting
     * @throws UnknownEntityException
     */
    protected function findSetting(UuidInterface $settingId): Setting
    {
        foreach ($this->currentUser->getSettings() as $setting) {
            if ($setting->getId()->compareTo($settingId) === 0) {
                return $setting;
            }
        }

        throw new UnknownEntityException('setting', $settingId->toString());
    }
}
