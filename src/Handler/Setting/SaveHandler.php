<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Setting;

use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\CombinationStatus;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
use FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper;
use FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingOptionsData;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * The handler for saving changes in the options for a setting.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SaveHandler implements RequestHandlerInterface
{
    private CombinationHelper $combinationHelper;
    private User $currentUser;
    private SettingRepository $settingRepository;
    private SidebarEntitiesHelper $sidebarEntitiesHelper;

    public function __construct(
        CombinationHelper $combinationHelper,
        User $currentUser,
        SettingRepository $settingRepository,
        SidebarEntitiesHelper $sidebarEntitiesHelper
    ) {
        $this->combinationHelper = $combinationHelper;
        $this->currentUser = $currentUser;
        $this->settingRepository = $settingRepository;
        $this->sidebarEntitiesHelper = $sidebarEntitiesHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $combinationId = Uuid::fromString($request->getAttribute('combination-id', ''));
        /** @var SettingOptionsData $requestOptions */
        $requestOptions = $request->getParsedBody();

        $setting = $this->currentUser->getSettingByCombinationId($combinationId);
        if ($setting === null) {
            $setting = $this->createSetting($combinationId);
        }

        $setting->setName($requestOptions->name)
                ->setLocale($requestOptions->locale)
                ->setRecipeMode($requestOptions->recipeMode)
                ->setIsTemporary(false);

        $this->sidebarEntitiesHelper->refreshLabels($setting);
        return new EmptyResponse();
    }

    /**
     * @param UuidInterface $combinationId
     * @return Setting
     * @throws Exception
     */
    private function createSetting(UuidInterface $combinationId): Setting
    {
        $combination = $this->combinationHelper->createForCombinationId($combinationId);
        if ($combination->getStatus() === CombinationStatus::UNKNOWN) {
            $this->combinationHelper->triggerExport($combination);
        }

        return $this->settingRepository->createSetting($this->currentUser, $combination);
    }
}
