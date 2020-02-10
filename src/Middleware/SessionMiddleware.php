<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Middleware;

use DateTime;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Repository\UserRepository;
use Laminas\ServiceManager\ServiceManager;
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
        $userId = $this->readUserIdFromCookie($request);
        $user = $this->fetchUser($userId);
        $this->injectUserToServiceManager($user);

        $response = $handler->handle($request);

        $this->userRepository->persist($user); // @todo Decide whether we actually want to persist the current user.
        return $this->injectCookieIntoResponse($response, $this->createCookie($user->getId()));
    }

    /**
     * Reads the user id from the cookie.
     * @param ServerRequestInterface $request
     * @return UuidInterface|null
     * @throws Exception
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
     * Fetches the user to the specified id.
     * @param UuidInterface|null $userId
     * @return User
     */
    protected function fetchUser(?UuidInterface $userId): User
    {
        $result = null;
        if ($userId !== null) {
            $result = $this->userRepository->getUser($userId);
        }
        if ($result === null) {
            $result = $this->userRepository->createUser();
            if ($userId !== null) {
                $result->setId($userId);
            }
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
     * Creates the cookie with the user id to place into the response.
     * @param UuidInterface $userId
     * @return SetCookie
     * @throws Exception
     */
    protected function createCookie(UuidInterface $userId): SetCookie
    {
        return SetCookie::create($this->cookieName, $userId->toString())
            ->withDomain($this->cookieDomain)
            ->withPath($this->cookiePath)
            ->withExpires(new DateTime($this->cookieLifeTime));
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
