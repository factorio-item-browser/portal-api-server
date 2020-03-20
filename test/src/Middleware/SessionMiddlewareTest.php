<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Middleware;

use BluePsyduck\TestHelper\ReflectionTrait;
use Dflydev\FigCookies\SetCookie;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\RouteName;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\MissingSessionException;
use FactorioItemBrowser\PortalApi\Server\Middleware\SessionMiddleware;
use FactorioItemBrowser\PortalApi\Server\Repository\UserRepository;
use GuzzleHttp\Psr7\Response;
use Laminas\Diactoros\ServerRequest;
use Laminas\ServiceManager\ServiceManager;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ReflectionException;

/**
 * The PHPUnit test of the SessionMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Middleware\SessionMiddleware
 */
class SessionMiddlewareTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked service manager.
     * @var ServiceManager&MockObject
     */
    protected $serviceManager;

    /**
     * The mocked user repository.
     * @var UserRepository&MockObject
     */
    protected $userRepository;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceManager = $this->createMock(ServiceManager::class);
        $this->userRepository = $this->createMock(UserRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $cookieName = 'abc';
        $cookieDomain = 'def';
        $cookiePath = 'ghi';
        $cookieLifeTime = 'jkl';

        $middleware = new SessionMiddleware(
            $this->serviceManager,
            $this->userRepository,
            $cookieName,
            $cookieDomain,
            $cookiePath,
            $cookieLifeTime
        );

        $this->assertSame($this->serviceManager, $this->extractProperty($middleware, 'serviceManager'));
        $this->assertSame($this->userRepository, $this->extractProperty($middleware, 'userRepository'));
        $this->assertSame($cookieName, $this->extractProperty($middleware, 'cookieName'));
        $this->assertSame($cookieDomain, $this->extractProperty($middleware, 'cookieDomain'));
        $this->assertSame($cookiePath, $this->extractProperty($middleware, 'cookiePath'));
        $this->assertSame($cookieLifeTime, $this->extractProperty($middleware, 'cookieLifeTime'));
    }

    /**
     * Tests the process method.
     * @throws Exception
     * @covers ::process
     */
    public function testProcess(): void
    {
        /* @var User&MockObject $user */
        $user = $this->createMock(User::class);
        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var ResponseInterface&MockObject $response1 */
        $response1 = $this->createMock(ResponseInterface::class);
        /* @var ResponseInterface&MockObject $response2 */
        $response2 = $this->createMock(ResponseInterface::class);
        /* @var SetCookie&MockObject $cookie */
        $cookie = $this->createMock(SetCookie::class);

        /* @var RequestHandlerInterface&MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response1);

        $this->userRepository->expects($this->once())
                             ->method('persist')
                             ->with($this->identicalTo($user));

        /* @var SessionMiddleware&MockObject $middleware */
        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods([
                               'readUserFromRequest',
                               'injectUserToServiceManager',
                               'createCookie',
                               'injectCookieIntoResponse',
                           ])
                           ->setConstructorArgs([$this->serviceManager, $this->userRepository, '', '', '', ''])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('readUserFromRequest')
                   ->with($this->identicalTo($request))
                   ->willReturn($user);
        $middleware->expects($this->once())
                   ->method('injectUserToServiceManager')
                   ->with($this->identicalTo($user));
        $middleware->expects($this->once())
                   ->method('createCookie')
                   ->with($this->identicalTo($user))
                   ->willReturn($cookie);
        $middleware->expects($this->once())
                   ->method('injectCookieIntoResponse')
                   ->with($this->identicalTo($response1), $this->identicalTo($cookie))
                   ->willReturn($response2);

        $result = $middleware->process($request, $handler);

        $this->assertSame($response2, $result);
    }

    /**
     * Tests the readUserFromRequest method.
     * @throws ReflectionException
     * @covers ::readUserFromRequest
     */
    public function testReadUserFromRequestWithExistingUser(): void
    {
        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var UuidInterface&MockObject $userId */
        $userId = $this->createMock(UuidInterface::class);
        /* @var User&MockObject $user */
        $user = $this->createMock(User::class);

        $this->userRepository->expects($this->once())
                             ->method('getUser')
                             ->with($this->identicalTo($userId))
                             ->willReturn($user);
        $this->userRepository->expects($this->never())
                             ->method('createUser');

        /* @var SessionMiddleware&MockObject $middleware */
        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['readUserIdFromCookie', 'isRouteWhitelisted'])
                           ->setConstructorArgs([$this->serviceManager, $this->userRepository, '', '', '', ''])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('readUserIdFromCookie')
                   ->with($this->identicalTo($request))
                   ->willReturn($userId);
        $middleware->expects($this->never())
                   ->method('isRouteWhitelisted');

        $result = $this->invokeMethod($middleware, 'readUserFromRequest', $request);

        $this->assertSame($user, $result);
    }

    /**
     * Tests the readUserFromRequest method.
     * @throws ReflectionException
     * @covers ::readUserFromRequest
     */
    public function testReadUserFromRequestWithNewUser(): void
    {
        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var UuidInterface&MockObject $userId */
        $userId = $this->createMock(UuidInterface::class);
        /* @var User&MockObject $user */
        $user = $this->createMock(User::class);

        $this->userRepository->expects($this->once())
                             ->method('getUser')
                             ->with($this->identicalTo($userId))
                             ->willReturn(null);
        $this->userRepository->expects($this->once())
                             ->method('createUser')
                             ->willReturn($user);

        /* @var SessionMiddleware&MockObject $middleware */
        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['readUserIdFromCookie', 'isRouteWhitelisted'])
                           ->setConstructorArgs([$this->serviceManager, $this->userRepository, '', '', '', ''])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('readUserIdFromCookie')
                   ->with($this->identicalTo($request))
                   ->willReturn($userId);
        $middleware->expects($this->once())
                   ->method('isRouteWhitelisted')
                   ->with($this->identicalTo($request))
                   ->willReturn(true);

        $result = $this->invokeMethod($middleware, 'readUserFromRequest', $request);

        $this->assertSame($user, $result);
    }

    /**
     * Tests the readUserFromRequest method.
     * @throws ReflectionException
     * @covers ::readUserFromRequest
     */
    public function testReadUserFromRequestWithoutUserId(): void
    {
        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var User&MockObject $user */
        $user = $this->createMock(User::class);

        $this->userRepository->expects($this->never())
                             ->method('getUser');
        $this->userRepository->expects($this->once())
                             ->method('createUser')
                             ->willReturn($user);

        /* @var SessionMiddleware&MockObject $middleware */
        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['readUserIdFromCookie', 'isRouteWhitelisted'])
                           ->setConstructorArgs([$this->serviceManager, $this->userRepository, '', '', '', ''])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('readUserIdFromCookie')
                   ->with($this->identicalTo($request))
                   ->willReturn(null);
        $middleware->expects($this->once())
                   ->method('isRouteWhitelisted')
                   ->with($this->identicalTo($request))
                   ->willReturn(true);

        $result = $this->invokeMethod($middleware, 'readUserFromRequest', $request);

        $this->assertSame($user, $result);
    }

    /**
     * Tests the readUserFromRequest method.
     * @throws ReflectionException
     * @covers ::readUserFromRequest
     */
    public function testReadUserFromRequestWithException(): void
    {
        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);

        $this->userRepository->expects($this->never())
                             ->method('getUser');
        $this->userRepository->expects($this->never())
                             ->method('createUser');

        $this->expectException(MissingSessionException::class);

        /* @var SessionMiddleware&MockObject $middleware */
        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['readUserIdFromCookie', 'isRouteWhitelisted'])
                           ->setConstructorArgs([$this->serviceManager, $this->userRepository, '', '', '', ''])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('readUserIdFromCookie')
                   ->with($this->identicalTo($request))
                   ->willReturn(null);
        $middleware->expects($this->once())
                   ->method('isRouteWhitelisted')
                   ->with($this->identicalTo($request))
                   ->willReturn(false);

        $this->invokeMethod($middleware, 'readUserFromRequest', $request);
    }

    /**
     * Tests the readUserIdFromCookie method.
     * @throws ReflectionException
     * @covers ::readUserIdFromCookie
     */
    public function testReadUserIdFromCookie(): void
    {
        $cookieName = 'foo';
        $userIdString = 'bdec7a85-5de5-49b9-9634-8b12319fa212';

        $request = new ServerRequest();
        $request = $request->withHeader('Cookie', "{$cookieName}={$userIdString}");

        $middleware = new SessionMiddleware($this->serviceManager, $this->userRepository, $cookieName, '', '', '');

        /* @var UuidInterface $result */
        $result = $this->invokeMethod($middleware, 'readUserIdFromCookie', $request);

        $this->assertInstanceOf(UuidInterface::class, $result);
        $this->assertSame($userIdString, $result->toString());
    }

    /**
     * Tests the readUserIdFromCookie method.
     * @throws ReflectionException
     * @covers ::readUserIdFromCookie
     */
    public function testReadUserIdFromCookieWithException(): void
    {
        $cookieName = 'foo';

        $request = new ServerRequest();
        $request = $request->withHeader('Cookie', "{$cookieName}=xyz");

        $middleware = new SessionMiddleware($this->serviceManager, $this->userRepository, $cookieName, '', '', '');
        $result = $this->invokeMethod($middleware, 'readUserIdFromCookie', $request);

        $this->assertNull($result);
    }

    /**
     * Provides the data for the isRouteWhitelisted test.
     * @return array<mixed>
     */
    public function provideIsRouteWhitelisted(): array
    {
        return [
            [RouteName::SESSION_INIT, true],
            [RouteName::RANDOM, false],
        ];
    }

    /**
     * Tests the isRouteWhitelisted method.
     * @param string $routeName
     * @param bool $expectedResult
     * @throws ReflectionException
     * @covers ::isRouteWhitelisted
     * @dataProvider provideIsRouteWhitelisted
     */
    public function testIsRouteWhitelisted(string $routeName, bool $expectedResult): void
    {
        /* @var Route&MockObject $route */
        $route = $this->createMock(Route::class);
        $route->expects($this->once())
              ->method('getName')
              ->willReturn($routeName);

        /* @var RouteResult&MockObject $routeResult */
        $routeResult = $this->createMock(RouteResult::class);
        $routeResult->expects($this->once())
                    ->method('getMatchedRoute')
                    ->willReturn($route);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo(RouteResult::class))
                ->willReturn($routeResult);

        $middleware = new SessionMiddleware($this->serviceManager, $this->userRepository, '', '', '', '');
        $result = $this->invokeMethod($middleware, 'isRouteWhitelisted', $request);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the injectUserToServiceManager method.
     * @throws ReflectionException
     * @covers ::injectUserToServiceManager
     */
    public function testInjectUserToServiceManager(): void
    {
        /* @var Setting&MockObject $currentSetting */
        $currentSetting = $this->createMock(Setting::class);

        /* @var User&MockObject $user */
        $user = $this->createMock(User::class);
        $user->expects($this->any())
             ->method('getCurrentSetting')
             ->willReturn($currentSetting);

        $this->serviceManager->expects($this->exactly(2))
                             ->method('setService')
                             ->withConsecutive(
                                 [
                                     $this->identicalTo(User::class . ' $currentUser'),
                                     $this->identicalTo($user),
                                 ],
                                 [
                                     $this->identicalTo(Setting::class . ' $currentSetting'),
                                     $this->identicalTo($currentSetting),
                                 ]
                             );

        $middleware = new SessionMiddleware($this->serviceManager, $this->userRepository, '', '', '', '');
        $this->invokeMethod($middleware, 'injectUserToServiceManager', $user);
    }

    /**
     * Tests the createCookie method.
     * @throws ReflectionException
     * @covers ::createCookie
     */
    public function testCreateCookie(): void
    {
        $cookieName = 'abc';
        $cookieDomain = 'def';
        $cookiePath = 'ghi';
        $cookieLifeTime = '+1 hour';

        $userIdString = 'bdec7a85-5de5-49b9-9634-8b12319fa212';
        $userId = Uuid::fromString($userIdString);

        /* @var User&MockObject $user */
        $user = $this->createMock(User::class);
        $user->expects($this->once())
             ->method('getId')
             ->willReturn($userId);

        $middleware = new SessionMiddleware(
            $this->serviceManager,
            $this->userRepository,
            $cookieName,
            $cookieDomain,
            $cookiePath,
            $cookieLifeTime
        );
        /* @var SetCookie $result */
        $result = $this->invokeMethod($middleware, 'createCookie', $user);

        $this->assertSame($cookieName, $result->getName());
        $this->assertSame($userIdString, $result->getValue());
        $this->assertSame($cookieDomain, $result->getDomain());
        $this->assertSame($cookiePath, $result->getPath());
        $this->assertLessThanOrEqual(time() + 3600, $result->getExpires());
    }

    /**
     * Tests the injectCookieIntoResponse method.
     * @throws ReflectionException
     * @covers ::injectCookieIntoResponse
     */
    public function testInjectCookieIntoResponse(): void
    {
        $cookie = SetCookie::create('foo', 'bar');
        $response = new Response();

        $middleware = new SessionMiddleware($this->serviceManager, $this->userRepository, '', '', '', '');

        /* @var ResponseInterface $result*/
        $result = $this->invokeMethod($middleware, 'injectCookieIntoResponse', $response, $cookie);

        $headerLine = $result->getHeaderLine('Set-Cookie');
        $this->assertStringContainsString('foo', $headerLine);
        $this->assertStringContainsString('bar', $headerLine);
    }
}
