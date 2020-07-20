<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Middleware;

use Dflydev\FigCookies\FigRequestCookies;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\RouteName;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\MissingSessionException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
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
     * The service manager.
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * The user repository.
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * The name to use for the cookie.
     * @var string
     */
    protected $cookieName;

    /**
     * Initializes the middleware.
     * @param ServiceManager $serviceManager
     * @param UserRepository $userRepository
     * @param string $sessionCookieName
     */
    public function __construct(
        ServiceManager $serviceManager,
        UserRepository $userRepository,
        string $sessionCookieName
    ) {
        $this->serviceManager = $serviceManager;
        $this->userRepository = $userRepository;
        $this->cookieName = $sessionCookieName;
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

        $this->serviceManager->setService(User::class . ' $currentUser', $user);
        $this->serviceManager->setService(Setting::class . ' $currentSetting', $setting);

        $response = $handler->handle($request);

        $this->userRepository->persist($user);
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
        $user = $this->readUserFromRequest($request);
        if ($user === null) {
            if (!$this->isInitRoute($request)) {
                throw new MissingSessionException();
            }

            $user = $this->userRepository->createUser();
        }

        return $user;
    }

    /**
     * Reads the user from the request.
     * @param ServerRequestInterface $request
     * @return User
     * @throws Exception
     */
    protected function readUserFromRequest(ServerRequestInterface $request): ?User
    {
        $userId = $this->readIdFromHeader($request, 'user-id');
        if ($userId === null) {
            $userId = $this->readIdFromCookie($request, $this->cookieName);
        }

        if ($userId !== null) {
            return $this->userRepository->getUser($userId);
        }
        return null;
    }

    /**
     * Returns the current setting for the request.
     * @param ServerRequestInterface $request
     * @param User $user
     * @return Setting
     * @throws PortalApiServerException
     * @codeCoverageIgnore Method will be changed again at a later point, no need to test it yet.
     */
    protected function getCurrentSetting(ServerRequestInterface $request, User $user): Setting
    {
        $isInitRoute = $this->isInitRoute($request);

        $setting = $this->readSettingFromRequest($request, $user);
        if ($setting === null) {
            if (!$isInitRoute) {
                // @todo Add temporary setting.
                throw new PortalApiServerException('Invalid combination', 400);
            }
            /** @var Setting $setting */
            $setting = $user->getCurrentSetting();
        }

        if ($isInitRoute) {
            $user->setCurrentSetting($setting);
        }

        return $setting;
    }

    /**
     * Reads the setting to use from the request.
     * @param ServerRequestInterface $request
     * @param User $user
     * @return Setting|null
     */
    protected function readSettingFromRequest(ServerRequestInterface $request, User $user): ?Setting
    {
        $combinationId = $this->readIdFromHeader($request, 'combination-id');
        if ($combinationId === null) {
            return null;
        }

        foreach ($user->getSettings() as $setting) {
            if ($combinationId->equals($setting->getCombination()->getId())) {
                return $setting;
            }
        }

        return null;
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
     * Reads an id from a cookie.
     * @param ServerRequestInterface $request
     * @param string $name
     * @return UuidInterface|null
     */
    protected function readIdFromCookie(ServerRequestInterface $request, string $name): ?UuidInterface
    {
        try {
            return Uuid::fromString((string) FigRequestCookies::get($request, $name)->getValue());
        } catch (Exception $e) {
            return null;
        }
    }
}
