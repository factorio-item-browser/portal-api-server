<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Settings;

use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\CombinationStatus;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
use FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingCreateData;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The handler for creating a new setting.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CreateHandler implements RequestHandlerInterface
{
    private CombinationHelper $combinationHelper;
    private User $currentUser;
    private SettingRepository $settingRepository;

    public function __construct(
        CombinationHelper $combinationHelper,
        User $currentUser,
        SettingRepository $settingRepository
    ) {
        $this->combinationHelper = $combinationHelper;
        $this->currentUser = $currentUser;
        $this->settingRepository = $settingRepository;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var SettingCreateData $settingData */
        $settingData = $request->getParsedBody();

        $combination = $this->combinationHelper->createForModNames($settingData->modNames);
        if ($combination->getStatus() === CombinationStatus::UNKNOWN) {
            $this->combinationHelper->triggerExport($combination);
        }

        $setting = $this->settingRepository->createSetting($this->currentUser, $combination);
        $setting->setName($settingData->name)
                ->setRecipeMode($settingData->recipeMode)
                ->setLocale($settingData->locale);

        return new EmptyResponse();
    }
}
