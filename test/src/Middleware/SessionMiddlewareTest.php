<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Middleware;

use BluePsyduck\TestHelper\ReflectionTrait;
use Dflydev\FigCookies\Modifier\SameSite;
use Dflydev\FigCookies\SetCookie;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\RouteName;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
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
            $cookieLifeTime,
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
        $user = $this->createMock(User::class);
        $setting = $this->createMock(Setting::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $response1 = $this->createMock(ResponseInterface::class);
        $response2 = $this->createMock(ResponseInterface::class);
        $cookie = $this->createMock(SetCookie::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response1);

        $this->serviceManager->expects($this->exactly(2))
                             ->method('setService')
                             ->withConsecutive(
                                 [
                                     $this->identicalTo(User::class . ' $currentUser'),
                                     $this->identicalTo($user),
                                 ],
                                 [
                                     $this->identicalTo(Setting::class . ' $currentSetting'),
                                     $this->identicalTo($setting),
                                 ],
                             );

        $this->userRepository->expects($this->once())
                             ->method('persist')
                             ->with($this->identicalTo($user));

        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods([
                               'getCurrentUser',
                               'getCurrentSetting',
                               'createCookie',
                               'injectCookieIntoResponse',
                           ])
                           ->setConstructorArgs([$this->serviceManager, $this->userRepository, '', '', '', ''])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('getCurrentUser')
                   ->with($this->identicalTo($request))
                   ->willReturn($user);
        $middleware->expects($this->once())
                   ->method('getCurrentSetting')
                   ->with($this->identicalTo($request), $this->identicalTo($user))
                   ->willReturn($setting);
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
     * Provides the data for the isInitRoute test.
     * @return array<mixed>
     */
    public function provideIsInitRoute(): array
    {
        return [
            [RouteName::INIT, true],
            [RouteName::RANDOM, false],
        ];
    }

    /**
     * Tests the isInitRoute method.
     * @param string $routeName
     * @param bool $expectedResult
     * @throws ReflectionException
     * @covers ::isInitRoute
     * @dataProvider provideIsInitRoute
     */
    public function testIsRouteWhitelisted(string $routeName, bool $expectedResult): void
    {
        $route = $this->createMock(Route::class);
        $route->expects($this->once())
              ->method('getName')
              ->willReturn($routeName);

        $routeResult = $this->createMock(RouteResult::class);
        $routeResult->expects($this->once())
                    ->method('getMatchedRoute')
                    ->willReturn($route);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo(RouteResult::class))
                ->willReturn($routeResult);

        $middleware = new SessionMiddleware($this->serviceManager, $this->userRepository, '', '', '', '');
        $result = $this->invokeMethod($middleware, 'isInitRoute', $request);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getCurrentUser method.
     * @throws ReflectionException
     * @covers ::getCurrentUser
     */
    public function testGetCurrentUser(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $user = $this->createMock(User::class);


        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['readUserFromRequest'])
                           ->setConstructorArgs([$this->serviceManager, $this->userRepository, '', '', '', ''])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('readUserFromRequest')
                   ->with($this->identicalTo($request))
                   ->willReturn($user);

        $result = $this->invokeMethod($middleware, 'getCurrentUser', $request);

        $this->assertSame($user, $result);
    }

    /**
     * Tests the getCurrentUser method.
     * @throws ReflectionException
     * @covers ::getCurrentUser
     */
    public function testGetCurrentUserWithoutExistingUser(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['readUserFromRequest', 'isInitRoute'])
                           ->setConstructorArgs([$this->serviceManager, $this->userRepository, '', '', '', ''])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('readUserFromRequest')
                   ->with($this->identicalTo($request))
                   ->willReturn(null);
        $middleware->expects($this->once())
                   ->method('isInitRoute')
                   ->with($this->identicalTo($request))
                   ->willReturn(false);

        $this->expectException(MissingSessionException::class);

        $this->invokeMethod($middleware, 'getCurrentUser', $request);
    }

    /**
     * Tests the getCurrentUser method.
     * @throws ReflectionException
     * @covers ::getCurrentUser
     */
    public function testGetCurrentUserWithNewUser(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $user = $this->createMock(User::class);

        $this->userRepository->expects($this->once())
                             ->method('createUser')
                             ->willReturn($user);


        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['readUserFromRequest', 'isInitRoute'])
                           ->setConstructorArgs([$this->serviceManager, $this->userRepository, '', '', '', ''])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('readUserFromRequest')
                   ->with($this->identicalTo($request))
                   ->willReturn(null);
        $middleware->expects($this->once())
                   ->method('isInitRoute')
                   ->with($this->identicalTo($request))
                   ->willReturn(true);

        $result = $this->invokeMethod($middleware, 'getCurrentUser', $request);
        
        $this->assertSame($user, $result);
    }

    /**
     * Tests the readUserFromRequest method.
     * @throws ReflectionException
     * @covers ::readUserFromRequest
     */
    public function testReadUserFromRequest(): void
    {
        $cookieName = 'abc';
        $request = $this->createMock(ServerRequestInterface::class);
        $userId = $this->createMock(UuidInterface::class);
        $user = $this->createMock(User::class);

        $this->userRepository->expects($this->once())
                             ->method('getUser')
                             ->with($this->identicalTo($userId))
                             ->willReturn($user);

        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['readIdFromCookie'])
                           ->setConstructorArgs([$this->serviceManager, $this->userRepository, $cookieName, '', '', ''])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('readIdFromCookie')
                   ->with($this->identicalTo($request), $this->identicalTo($cookieName))
                   ->willReturn($userId);

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
        $cookieName = 'abc';
        $request = $this->createMock(ServerRequestInterface::class);

        $this->userRepository->expects($this->never())
                             ->method('getUser');

        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['readIdFromCookie'])
                           ->setConstructorArgs([$this->serviceManager, $this->userRepository, $cookieName, '', '', ''])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('readIdFromCookie')
                   ->with($this->identicalTo($request), $this->identicalTo($cookieName))
                   ->willReturn(null);

        $result = $this->invokeMethod($middleware, 'readUserFromRequest', $request);

        $this->assertNull($result);
    }

    /**
     * Tests the readSettingFromRequest method.
     * @throws ReflectionException
     * @covers ::readSettingFromRequest
     */
    public function testReadSettingFromRequest(): void
    {
        $combinationId1 = '10cfc610-7bf5-4ea8-a7d0-fc1adb717035';
        $combination1 = new Combination();
        $combination1->setId(Uuid::fromString($combinationId1));
        $setting1 = new Setting();
        $setting1->setCombination($combination1);

        $combinationId2 = '248e3753-14b9-4b6d-a09d-356e190dc31f';
        $combination2 = new Combination();
        $combination2->setId(Uuid::fromString($combinationId2));
        $setting2 = new Setting();
        $setting2->setCombination($combination2);

        $request = $this->createMock(ServerRequestInterface::class);
        $combinationId = Uuid::fromString($combinationId2);
        $expectedResult = $setting2;

        $user = $this->createMock(User::class);
        $user->expects($this->once())
             ->method('getSettings')
             ->willReturn(new ArrayCollection([$setting1, $setting2]));

        /* @var SessionMiddleware&MockObject $middleware */
        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['readIdFromHeader'])
                           ->setConstructorArgs([$this->serviceManager, $this->userRepository, '', '', '', ''])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('readIdFromHeader')
                   ->with($this->identicalTo($request), $this->identicalTo('combination-id'))
                   ->willReturn($combinationId);

        $result = $this->invokeMethod($middleware, 'readSettingFromRequest', $request, $user);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the readSettingFromRequest method.
     * @throws ReflectionException
     * @covers ::readSettingFromRequest
     */
    public function testReadSettingFromRequestWithoutSetting(): void
    {
        $combinationId1 = '10cfc610-7bf5-4ea8-a7d0-fc1adb717035';
        $combination1 = new Combination();
        $combination1->setId(Uuid::fromString($combinationId1));
        $setting1 = new Setting();
        $setting1->setCombination($combination1);

        $combinationId2 = '248e3753-14b9-4b6d-a09d-356e190dc31f';
        $combination2 = new Combination();
        $combination2->setId(Uuid::fromString($combinationId2));
        $setting2 = new Setting();
        $setting2->setCombination($combination2);

        $request = $this->createMock(ServerRequestInterface::class);
        $combinationId = Uuid::fromString('f1ab3eef-2625-4e36-8772-768702900b91');

        $user = $this->createMock(User::class);
        $user->expects($this->once())
             ->method('getSettings')
             ->willReturn(new ArrayCollection([$setting1, $setting2]));

        /* @var SessionMiddleware&MockObject $middleware */
        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['readIdFromHeader'])
                           ->setConstructorArgs([$this->serviceManager, $this->userRepository, '', '', '', ''])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('readIdFromHeader')
                   ->with($this->identicalTo($request), $this->identicalTo('combination-id'))
                   ->willReturn($combinationId);

        $result = $this->invokeMethod($middleware, 'readSettingFromRequest', $request, $user);

        $this->assertNull($result);
    }

    /**
     * Tests the readSettingFromRequest method.
     * @throws ReflectionException
     * @covers ::readSettingFromRequest
     */
    public function testReadSettingFromRequestWithoutCombinationId(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $user = $this->createMock(User::class);

        /* @var SessionMiddleware&MockObject $middleware */
        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['readIdFromHeader'])
                           ->setConstructorArgs([$this->serviceManager, $this->userRepository, '', '', '', ''])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('readIdFromHeader')
                   ->with($this->identicalTo($request), $this->identicalTo('combination-id'))
                   ->willReturn(null);

        $result = $this->invokeMethod($middleware, 'readSettingFromRequest', $request, $user);

        $this->assertNull($result);
    }

    /**
     * Tests the readIdFromHeader method.
     * @throws ReflectionException
     * @covers ::readIdFromHeader
     */
    public function testReadIdFromHeader(): void
    {
        $name = 'abc';
        $id = 'bdec7a85-5de5-49b9-9634-8b12319fa212';

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getHeaderLine')
                ->with($this->identicalTo($name))
                ->willReturn($id);

        $middleware = new SessionMiddleware($this->serviceManager, $this->userRepository, '', '', '', '');
        $result = $this->invokeMethod($middleware, 'readIdFromHeader', $request, $name);

        $this->assertEquals(Uuid::fromString($id), $result);
    }

    /**
     * Tests the readIdFromHeader method.
     * @throws ReflectionException
     * @covers ::readIdFromHeader
     */
    public function testReadIdFromHeaderWithException(): void
    {
        $name = 'abc';
        $id = 'xyz';

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getHeaderLine')
                ->with($this->identicalTo($name))
                ->willReturn($id);

        $middleware = new SessionMiddleware($this->serviceManager, $this->userRepository, '', '', '', '');
        $result = $this->invokeMethod($middleware, 'readIdFromHeader', $request, $name);

        $this->assertNull($result);
    }

    /**
     * Tests the readUserIdFromCookie method.
     * @throws ReflectionException
     * @covers ::readIdFromCookie
     */
    public function testReadIdFromCookie(): void
    {
        $name = 'abc';
        $id = 'bdec7a85-5de5-49b9-9634-8b12319fa212';

        $request = new ServerRequest();
        $request = $request->withHeader('Cookie', "{$name}={$id}");

        $middleware = new SessionMiddleware($this->serviceManager, $this->userRepository, '', '', '', '');
        $result = $this->invokeMethod($middleware, 'readIdFromCookie', $request, $name);

        $this->assertEquals(Uuid::fromString($id), $result);
    }

    /**
     * Tests the readIdFromCookie method.
     * @throws ReflectionException
     * @covers ::readIdFromCookie
     */
    public function testReadIdFromCookieWithException(): void
    {
        $name = 'foo';
        $id = 'xyz';

        $request = new ServerRequest();
        $request = $request->withHeader('Cookie', "{$name}={$id}");

        $middleware = new SessionMiddleware($this->serviceManager, $this->userRepository, '', '', '', '');
        $result = $this->invokeMethod($middleware, 'readIdFromCookie', $request, $name);

        $this->assertNull($result);
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
        $this->assertTrue($result->getSecure());
        $this->assertTrue($result->getHttpOnly());
        $this->assertEquals(SameSite::strict(), $result->getSameSite());
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
