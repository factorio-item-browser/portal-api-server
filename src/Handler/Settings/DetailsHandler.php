<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Settings;

use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Helper\SettingHelper;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

/**
 * The handler for requesting the details to a setting.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DetailsHandler implements RequestHandlerInterface
{
    /**
     * The setting helper.
     * @var SettingHelper
     */
    protected $settingHelper;

    /**
     * Initializes the handler.
     * @param SettingHelper $settingHelper
     */
    public function __construct(SettingHelper $settingHelper)
    {
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
        $settingId = $request->getAttribute('setting-id', '');
        $setting = $this->settingHelper->findInCurrentUser(Uuid::fromString($settingId));
        $settingDetails = $this->settingHelper->createSettingDetails($setting);
        return new TransferResponse($settingDetails);
    }
}
