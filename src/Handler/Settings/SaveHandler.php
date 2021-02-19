<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Settings;

use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\MissingSettingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingOptionsData;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

/**
 * The handler for saving changes in the options for a setting.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SaveHandler implements RequestHandlerInterface
{
    private User $currentUser;
    private SidebarEntitiesHelper $sidebarEntitiesHelper;

    public function __construct(
        User $currentUser,
        SidebarEntitiesHelper $sidebarEntitiesHelper
    ) {
        $this->currentUser = $currentUser;
        $this->sidebarEntitiesHelper = $sidebarEntitiesHelper;
    }

    /**
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

        /** @var SettingOptionsData $requestOptions */
        $requestOptions = $request->getParsedBody();

        $setting->setName($requestOptions->name)
                ->setLocale($requestOptions->locale)
                ->setRecipeMode($requestOptions->recipeMode)
                ->setIsTemporary(false);

        $this->sidebarEntitiesHelper->refreshLabels($setting);

        return new EmptyResponse();
    }
}
