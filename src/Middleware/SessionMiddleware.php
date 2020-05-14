<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Middleware;

use DateTime;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\Modifier\SameSite;
use Dflydev\FigCookies\SetCookie;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\RouteName;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\MissingSessionException;
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
     * The routes whitelisted to be valid without actual session.
     */
    protected const WHITELISTED_ROUTES = [
        RouteName::SESSION_INIT,
    ];

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
     * The domain to use for the cookie.
     * @var string
     */
    protected $cookieDomain;

    /**
     * The path to use for the cookie.
     * @var string
     */
    protected $cookiePath;

    /**
     * The lifetime to use for the cookie.
     * @var string
     */
    protected $cookieLifeTime;

    /**
     * Initializes the middleware.
     * @param ServiceManager $serviceManager
     * @param UserRepository $userRepository
     * @param string $sessionCookieName
     * @param string $sessionCookieDomain
     * @param string $sessionCookiePath
     * @param string $sessionCookieLifeTime
     */
    public function __construct(
        ServiceManager $serviceManager,
        UserRepository $userRepository,
        string $sessionCookieName,
        string $sessionCookieDomain,
        string $sessionCookiePath,
        string $sessionCookieLifeTime
    ) {
        $this->serviceManager = $serviceManager;
        $this->userRepository = $userRepository;
        $this->cookieName = $sessionCookieName;
        $this->cookieDomain = $sessionCookieDomain;
        $this->cookiePath = $sessionCookiePath;
        $this->cookieLifeTime = $sessionCookieLifeTime;
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
        $user = $this->readUserFromRequest($request);
        $this->injectUserToServiceManager($user);

        $response = $handler->handle($request);

        $this->userRepository->persist($user);
        return $this->injectCookieIntoResponse($response, $this->createCookie($user));
    }

    /**
     * Reads the user from the request.
     * @param ServerRequestInterface $request
     * @return User
     * @throws Exception
     */
    protected function readUserFromRequest(ServerRequestInterface $request): User
    {
        $user = null;
        $userId = $this->readUserIdFromCookie($request);
        if ($userId !== null) {
            $user = $this->userRepository->getUser($userId);
        }

        if ($user === null) {
            if (!$this->isRouteWhitelisted($request)) {
                throw new MissingSessionException();
            }
            $user = $this->userRepository->createUser();
        }

        return $user;
    }

    /**
     * Reads the user id from the cookie.
     * @param ServerRequestInterface $request
     * @return UuidInterface|null
     */
    protected function readUserIdFromCookie(ServerRequestInterface $request): ?UuidInterface
    {
        $result = null;
        try {
            $cookieValue = FigRequestCookies::get($request, $this->cookieName)->getValue();
            if ($cookieValue !== null) {
                $result = Uuid::fromString($cookieValue);
            }
        } catch (Exception $e) {
            // Invalid UUID, so do not use it.
        }
        return $result;
    }

    /**
     * Returns whether the route is whitelisted to create new users.
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function isRouteWhitelisted(ServerRequestInterface $request): bool
    {
        $result = false;

        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);
        $route = $routeResult->getMatchedRoute();
        if ($route instanceof Route) {
            $result = in_array($route->getName(), self::WHITELISTED_ROUTES, true);
        }
        return $result;
    }

    /**
     * Injects the user and its current setting into the service manager to be used by other classes.
     * @param User $user
     */
    protected function injectUserToServiceManager(User $user): void
    {
        $this->serviceManager->setService(User::class . ' $currentUser', $user);
        if ($user->getCurrentSetting() !== null) {
            $this->serviceManager->setService(Setting::class . ' $currentSetting', $user->getCurrentSetting());
        }
    }

    /**
     * Creates the cookie for the user to place into the response.
     * @param User $user
     * @return SetCookie
     * @throws Exception
     */
    protected function createCookie(User $user): SetCookie
    {
        return SetCookie::create($this->cookieName, $user->getId()->toString())
            ->withDomain($this->cookieDomain)
            ->withPath($this->cookiePath)
            ->withExpires(new DateTime($this->cookieLifeTime))
            ->withSecure(true)
            ->withSameSite(SameSite::strict());
    }

    /**
     * Injects the cookie into the response.
     * @param ResponseInterface $response
     * @param SetCookie $cookie
     * @return ResponseInterface
     */
    protected function injectCookieIntoResponse(ResponseInterface $response, SetCookie $cookie): ResponseInterface
    {
        return FigResponseCookies::set($response, $cookie);
    }
}
