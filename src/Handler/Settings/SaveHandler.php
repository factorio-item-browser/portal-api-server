<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Settings;

use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\RecipeMode;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\InvalidRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
use FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingOptionsData;
use JMS\Serializer\SerializerInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 *
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SaveHandler implements RequestHandlerInterface
{
    /**
     * The valid recipe modes.
     */
    protected const VALID_RECIPE_MODES = [
        RecipeMode::HYBRID,
        RecipeMode::NORMAL,
        RecipeMode::EXPENSIVE,
    ];

    /**
     * The current user.
     * @var User
     */
    protected $currentUser;

    /**
     * The serializer.
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * The sidebar entities helper.
     * @var SidebarEntitiesHelper
     */
    protected $sidebarEntitiesHelper;

    /**
     * Initializes the handler.
     * @param User $currentUser
     * @param SerializerInterface $portalApiServerSerializer
     * @param SidebarEntitiesHelper $sidebarEntitiesHelper
     */
    public function __construct(
        User $currentUser,
        SerializerInterface $portalApiServerSerializer,
        SidebarEntitiesHelper $sidebarEntitiesHelper
    ) {
        $this->currentUser = $currentUser;
        $this->serializer = $portalApiServerSerializer;
        $this->sidebarEntitiesHelper = $sidebarEntitiesHelper;
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
        $requestOptions = $this->parseRequestBody($request);
        $setting = $this->findSetting(Uuid::fromString($settingId));
        $this->validateOptions($requestOptions);

        $setting->setLocale($requestOptions->getLocale())
                ->setRecipeMode($requestOptions->getRecipeMode());

        $this->currentUser->setCurrentSetting($setting);
        $this->sidebarEntitiesHelper->refreshLabels($setting);

        return new EmptyResponse();
    }

    /**
     * Parses the body of the request.
     * @param ServerRequestInterface $request
     * @return SettingOptionsData
     * @throws PortalApiServerException
     */
    protected function parseRequestBody(ServerRequestInterface $request): SettingOptionsData
    {
        try {
            $requestBody = $request->getBody()->getContents();
            return $this->serializer->deserialize($requestBody, SettingOptionsData::class, 'json');
        } catch (Exception $e) {
            throw new InvalidRequestException($e->getMessage(), $e);
        }
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

    /**
     * Validates the option values.
     * @param SettingOptionsData $options
     * @throws PortalApiServerException
     */
    protected function validateOptions(SettingOptionsData $options): void
    {
        if (strlen($options->getLocale()) < 2 || strlen($options->getLocale()) > 5) {
            throw new InvalidRequestException('The specified locale is invalid.');
        }

        if (!in_array($options->getRecipeMode(), self::VALID_RECIPE_MODES, true)) {
            throw new InvalidRequestException('The specified recipeMode is invalid.');
        }
    }
}
