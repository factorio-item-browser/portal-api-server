<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Settings;

use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\RecipeMode;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\InvalidRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MissingSettingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingOptionsData;
use JMS\Serializer\SerializerInterface;
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
        $combinationId = Uuid::fromString($request->getAttribute('combination-id', ''));
        $setting = $this->currentUser->getSettingByCombinationId($combinationId);
        if ($setting === null) {
            throw new MissingSettingException($combinationId);
        }

        $requestOptions = $this->parseRequestBody($request);
        $this->validateOptions($requestOptions);
        $this->updateSetting($requestOptions, $setting);
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

    /**
     * Updates the setting to the request options.
     * @param SettingOptionsData $requestOptions
     * @param Setting $setting
     */
    protected function updateSetting(SettingOptionsData $requestOptions, Setting $setting): void
    {
        $setting->setName($requestOptions->getName())
                ->setLocale($requestOptions->getLocale())
                ->setRecipeMode($requestOptions->getRecipeMode())
                ->setIsTemporary(false);
    }
}
