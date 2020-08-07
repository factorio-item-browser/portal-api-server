<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Middleware;

use DateTime;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\RouteName;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\MissingCombinationIdException;
use FactorioItemBrowser\PortalApi\Server\Exception\MissingSessionException;
use FactorioItemBrowser\PortalApi\Server\Helper\CookieHelper;
use FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository;
use FactorioItemBrowser\PortalApi\Server\Repository\UserRepository;
use Laminas\ServiceManager\ServiceManager;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * The middleware handling the session and initializing the current user with its active setting.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SessionMiddleware implements MiddlewareInterface
{
    /**
     * The cookie helper.
     * @var CookieHelper
     */
    protected $cookieHelper;

    /**
     * The service manager.
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * The setting repository.
     * @var SettingRepository
     */
    protected $settingRepository;

    /**
     * The user repository.
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * Initializes the middleware.
     * @param CookieHelper $cookieHelper
     * @param ServiceManager $serviceManager
     * @param SettingRepository $settingRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        CookieHelper $cookieHelper,
        ServiceManager $serviceManager,
        SettingRepository $settingRepository,
        UserRepository $userRepository
    ) {
        $this->cookieHelper = $cookieHelper;
        $this->serviceManager = $serviceManager;
        $this->settingRepository = $settingRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating response creation to a handler.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->getCurrentUser($request);
        $setting = $this->getCurrentSetting($request, $user);
        $this->updateUser($request, $user, $setting);

        $this->serviceManager->setService(User::class . ' $currentUser', $user);
        $this->serviceManager->setService(Setting::class . ' $currentSetting', $setting);

        $response = $handler->handle($request);

        $this->userRepository->persist($user);
        $response = $this->cookieHelper->injectUser($response, $user);
        return $response;
    }

    /**
     * Returns whether the current route is the init route.
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function isInitRoute(ServerRequestInterface $request): bool
    {
        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);
        $route = $routeResult->getMatchedRoute();
        return $route instanceof Route && $route->getName() === RouteName::INIT;
    }

    /**
     * Returns the current user for the request.
     * @param ServerRequestInterface $request
     * @return User
     * @throws Exception
     */
    protected function getCurrentUser(ServerRequestInterface $request): User
    {
        $user = null;
        $userId = $this->cookieHelper->readUserId($request);
        if ($userId !== null) {
            $user = $this->userRepository->getUser($userId);
        }

        if ($user === null && !$this->isInitRoute($request)) {
            throw new MissingSessionException();
        }

        return $user === null ? $this->userRepository->createUser() : $user;
    }

    /**
     * Returns the current setting for the request.
     * @param ServerRequestInterface $request
     * @param User $user
     * @return Setting
     * @throws Exception
     */
    protected function getCurrentSetting(ServerRequestInterface $request, User $user): Setting
    {
        $combinationId = $this->readIdFromHeader($request, 'Combination-Id');
        if ($combinationId === null) {
            $currentSetting = $user->getCurrentSetting();
            if ($currentSetting === null || !$this->isInitRoute($request)) {
                throw new MissingCombinationIdException();
            }
            return $currentSetting;
        }

        foreach ($user->getSettings() as $setting) {
            if ($combinationId->equals($setting->getCombination()->getId())) {
                return $setting;
            }
        }

        $setting = $this->settingRepository->createTemporarySetting($user, $combinationId);
        $user->getSettings()->add($setting);

        return $setting;
    }

    /**
     * Reads an id from the header.
     * @param ServerRequestInterface $request
     * @param string $name
     * @return UuidInterface|null
     */
    protected function readIdFromHeader(ServerRequestInterface $request, string $name): ?UuidInterface
    {
        try {
            return Uuid::fromString($request->getHeaderLine($name));
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Updates the specified user and setting.
     * @param ServerRequestInterface $request
     * @param User $user
     * @param Setting $setting
     */
    protected function updateUser(ServerRequestInterface $request, User $user, Setting $setting): void
    {
        if ($this->isInitRoute($request)) {
            $user->setCurrentSetting($setting);
        }

        $setting->setLastUsageTime(new DateTime());
    }
}
