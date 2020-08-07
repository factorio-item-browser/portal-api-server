<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Helper;

use DateTime;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\Modifier\SameSite;
use Dflydev\FigCookies\SetCookie;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * The helper class managing the session cookie.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CookieHelper
{
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
     * Whether to use a secure cookie.
     * @var bool
     */
    protected $useSecureCookie;

    /**
     * Initializes the helper.
     * @param string $sessionCookieName
     * @param string $sessionCookieDomain
     * @param string $sessionCookiePath
     * @param string $sessionCookieLifeTime
     * @param bool $useSecureCookie
     */
    public function __construct(
        string $sessionCookieName,
        string $sessionCookieDomain,
        string $sessionCookiePath,
        string $sessionCookieLifeTime,
        bool $useSecureCookie
    ) {
        $this->cookieName = $sessionCookieName;
        $this->cookieDomain = $sessionCookieDomain;
        $this->cookiePath = $sessionCookiePath;
        $this->cookieLifeTime = $sessionCookieLifeTime;
        $this->useSecureCookie = $useSecureCookie;
    }

    /**
     * Reads the user id from the cookie.
     * @param ServerRequestInterface $request
     * @return UuidInterface|null
     */
    public function readUserId(ServerRequestInterface $request): ?UuidInterface
    {
        try {
            return Uuid::fromString((string) FigRequestCookies::get($request, $this->cookieName)->getValue());
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Injects the user as cookie into the response.
     * @param ResponseInterface $response
     * @param User $user
     * @return ResponseInterface
     * @throws Exception
     */
    public function injectUser(ResponseInterface $response, User $user): ResponseInterface
    {
        return FigResponseCookies::set($response, $this->createCookie($user));
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
            ->withSecure($this->useSecureCookie)
            ->withHttpOnly(true)
            ->withSameSite(SameSite::strict());
    }
}
