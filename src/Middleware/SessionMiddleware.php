<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Middleware;

use DateTime;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\CombinationStatus;
use FactorioItemBrowser\PortalApi\Server\Constant\RouteName;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedCombinationApiException;
use FactorioItemBrowser\PortalApi\Server\Exception\MissingCombinationIdException;
use FactorioItemBrowser\PortalApi\Server\Exception\MissingSessionException;
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
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
    protected CombinationHelper $combinationHelper;
    protected CookieHelper $cookieHelper;
    protected ServiceManager $serviceManager;
    protected SettingRepository $settingRepository;
    protected UserRepository $userRepository;

    public function __construct(
        CombinationHelper $combinationHelper,
        CookieHelper $cookieHelper,
        ServiceManager $serviceManager,
        SettingRepository $settingRepository,
        UserRepository $userRepository
    ) {
        $this->combinationHelper = $combinationHelper;
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
        $setting->setLastUsageTime(new DateTime());

        $this->serviceManager->setService(User::class . ' $currentUser', $user);
        $this->serviceManager->setService(Setting::class . ' $currentSetting', $setting);

        $response = $handler->handle($request);

        $this->userRepository->persist($user);
        return $this->cookieHelper->injectUser($response, $user);
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
            return $this->getFallbackSetting($request, $user);
        }

        foreach ($user->getSettings() as $setting) {
            if ($combinationId->equals($setting->getCombination()->getId())) {
                return $setting;
            }
        }

        try {
            $combination = $this->combinationHelper->createForCombinationId($combinationId);
        } catch (FailedCombinationApiException $e) {
            throw new UnknownEntityException('combination', $combinationId->toString(), $e);
        }
        if ($combination->getStatus() === CombinationStatus::UNKNOWN) {
            throw new UnknownEntityException('combination', $combinationId->toString());
        }

        $setting = $this->settingRepository->createTemporarySetting($user, $combination);
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
     * Returns the fallback setting to use.
     * @param ServerRequestInterface $request
     * @param User $user
     * @return Setting
     * @throws Exception
     */
    protected function getFallbackSetting(ServerRequestInterface $request, User $user): Setting
    {
        if (!$this->isInitRoute($request)) {
            throw new MissingCombinationIdException();
        }

        $setting = $user->getLastUsedSetting();
        if ($setting === null) {
            $setting = $this->settingRepository->createDefaultSetting($user);
            $user->getSettings()->add($setting);
        }
        return $setting;
    }
}
