<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Middleware;

use BluePsyduck\TestHelper\ReflectionTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\RouteName;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\MissingCombinationIdException;
use FactorioItemBrowser\PortalApi\Server\Exception\MissingSessionException;
use FactorioItemBrowser\PortalApi\Server\Helper\CookieHelper;
use FactorioItemBrowser\PortalApi\Server\Middleware\SessionMiddleware;
use FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository;
use FactorioItemBrowser\PortalApi\Server\Repository\UserRepository;
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
     * The mocked cookie helper.
     * @var CookieHelper&MockObject
     */
    protected $cookieHelper;

    /**
     * The mocked service manager.
     * @var ServiceManager&MockObject
     */
    protected $serviceManager;

    /**
     * The mocked setting repository.
     * @var SettingRepository&MockObject
     */
    protected $settingRepository;

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

        $this->cookieHelper = $this->createMock(CookieHelper::class);
        $this->serviceManager = $this->createMock(ServiceManager::class);
        $this->settingRepository = $this->createMock(SettingRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $middleware = new SessionMiddleware(
            $this->cookieHelper,
            $this->serviceManager,
            $this->settingRepository,
            $this->userRepository,
        );

        $this->assertSame($this->cookieHelper, $this->extractProperty($middleware, 'cookieHelper'));
        $this->assertSame($this->serviceManager, $this->extractProperty($middleware, 'serviceManager'));
        $this->assertSame($this->settingRepository, $this->extractProperty($middleware, 'settingRepository'));
        $this->assertSame($this->userRepository, $this->extractProperty($middleware, 'userRepository'));
    }

    /**
     * Tests the process method.
     * @throws Exception
     * @covers ::process
     */
    public function testProcess(): void
    {
        $user = $this->createMock(User::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $response1 = $this->createMock(ResponseInterface::class);
        $response2 = $this->createMock(ResponseInterface::class);

        $setting = $this->createMock(Setting::class);
        $setting->expects($this->once())
                ->method('setLastUsageTime')
                ->with($this->isInstanceOf(DateTime::class))
                ->willReturnSelf();

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

        $this->cookieHelper->expects($this->once())
                           ->method('injectUser')
                           ->with($this->identicalTo($response1), $this->identicalTo($user))
                           ->willReturn($response2);

        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods([
                               'getCurrentUser',
                               'getCurrentSetting',
                           ])
                           ->setConstructorArgs([
                               $this->cookieHelper,
                               $this->serviceManager,
                               $this->settingRepository,
                               $this->userRepository,
                           ])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('getCurrentUser')
                   ->with($this->identicalTo($request))
                   ->willReturn($user);
        $middleware->expects($this->once())
                   ->method('getCurrentSetting')
                   ->with($this->identicalTo($request), $this->identicalTo($user))
                   ->willReturn($setting);

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
    public function testIsInitRoute(string $routeName, bool $expectedResult): void
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

        $middleware = new SessionMiddleware(
            $this->cookieHelper,
            $this->serviceManager,
            $this->settingRepository,
            $this->userRepository,
        );
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
        $userId = $this->createMock(UuidInterface::class);
        $user = $this->createMock(User::class);

        $this->cookieHelper->expects($this->once())
                           ->method('readUserId')
                           ->with($this->identicalTo($request))
                           ->willReturn($userId);

        $this->userRepository->expects($this->once())
                             ->method('getUser')
                             ->with($this->identicalTo($userId))
                             ->willReturn($user);
        $this->userRepository->expects($this->never())
                             ->method('createUser');

        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['isInitRoute'])
                           ->setConstructorArgs([
                               $this->cookieHelper,
                               $this->serviceManager,
                               $this->settingRepository,
                               $this->userRepository,
                           ])
                           ->getMock();
        $middleware->expects($this->never())
                   ->method('isInitRoute');


        $result = $this->invokeMethod($middleware, 'getCurrentUser', $request);

        $this->assertSame($user, $result);
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

        $this->cookieHelper->expects($this->once())
                           ->method('readUserId')
                           ->with($this->identicalTo($request))
                           ->willReturn(null);

        $this->userRepository->expects($this->never())
                             ->method('getUser');
        $this->userRepository->expects($this->once())
                             ->method('createUser')
                             ->willReturn($user);

        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['isInitRoute'])
                           ->setConstructorArgs([
                               $this->cookieHelper,
                               $this->serviceManager,
                               $this->settingRepository,
                               $this->userRepository,
                           ])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('isInitRoute')
                   ->with($this->identicalTo($request))
                   ->willReturn(true);

        $result = $this->invokeMethod($middleware, 'getCurrentUser', $request);

        $this->assertSame($user, $result);
    }

    /**
     * Tests the getCurrentUser method.
     * @throws ReflectionException
     * @covers ::getCurrentUser
     */
    public function testGetCurrentUserWithMissingUser(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $userId = $this->createMock(UuidInterface::class);

        $this->cookieHelper->expects($this->once())
                           ->method('readUserId')
                           ->with($this->identicalTo($request))
                           ->willReturn($userId);

        $this->userRepository->expects($this->once())
                             ->method('getUser')
                             ->with($this->identicalTo($userId))
                             ->willReturn(null);
        $this->userRepository->expects($this->never())
                             ->method('createUser');

        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['isInitRoute'])
                           ->setConstructorArgs([
                               $this->cookieHelper,
                               $this->serviceManager,
                               $this->settingRepository,
                               $this->userRepository,
                           ])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('isInitRoute')
                   ->with($this->identicalTo($request))
                   ->willReturn(false);

        $this->expectException(MissingSessionException::class);

        $this->invokeMethod($middleware, 'getCurrentUser', $request);
    }

    /**
     * Tests the getCurrentSetting method.
     * @throws ReflectionException
     * @covers ::getCurrentSetting
     */
    public function testGetCurrentSettingWithExistingSetting(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $combinationId = Uuid::fromString('234edff6-efc6-474d-8cd8-b6391512325e');

        $combination1 = new Combination();
        $combination1->setId(Uuid::fromString('1cf0951d-0579-4523-99fd-9d50679b7ab6'));
        $setting1 = new Setting();
        $setting1->setCombination($combination1);

        $combination2 = new Combination();
        $combination2->setId(Uuid::fromString('234edff6-efc6-474d-8cd8-b6391512325e'));
        $setting2 = new Setting();
        $setting2->setCombination($combination2);

        $user = $this->createMock(User::class);
        $user->expects($this->any())
             ->method('getSettings')
             ->willReturn(new ArrayCollection([$setting1, $setting2]));

        $this->settingRepository->expects($this->never())
                                ->method('createTemporarySetting');

        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['readIdFromHeader', 'getFallbackSetting'])
                           ->setConstructorArgs([
                               $this->cookieHelper,
                               $this->serviceManager,
                               $this->settingRepository,
                               $this->userRepository,
                           ])
                           ->getMock();
        $middleware->expects($this->never())
                   ->method('getFallbackSetting');
        $middleware->expects($this->once())
                   ->method('readIdFromHeader')
                   ->with($this->identicalTo($request))
                   ->willReturn($combinationId);

        $result = $this->invokeMethod($middleware, 'getCurrentSetting', $request, $user);

        $this->assertSame($setting2, $result);
    }

    /**
     * Tests the getCurrentSetting method.
     * @throws ReflectionException
     * @covers ::getCurrentSetting
     */
    public function testGetCurrentSettingWithFallbackSetting(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $setting = $this->createMock(Setting::class);

        $user = $this->createMock(User::class);
        $user->expects($this->any())
             ->method('getSettings')
             ->willReturn(new ArrayCollection([]));

        $this->settingRepository->expects($this->never())
                                ->method('createTemporarySetting');

        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['readIdFromHeader', 'getFallbackSetting'])
                           ->setConstructorArgs([
                               $this->cookieHelper,
                               $this->serviceManager,
                               $this->settingRepository,
                               $this->userRepository,
                           ])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('readIdFromHeader')
                   ->with($this->identicalTo($request))
                   ->willReturn(null);
        $middleware->expects($this->once())
                   ->method('getFallbackSetting')
                   ->with($this->identicalTo($request), $this->identicalTo($user))
                   ->willReturn($setting);

        $result = $this->invokeMethod($middleware, 'getCurrentSetting', $request, $user);

        $this->assertSame($setting, $result);
    }

    /**
     * Tests the getCurrentSetting method.
     * @throws ReflectionException
     * @covers ::getCurrentSetting
     */
    public function testGetCurrentSettingWithTemporarySetting(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $combinationId = Uuid::fromString('234edff6-efc6-474d-8cd8-b6391512325e');
        $setting = $this->createMock(Setting::class);

        $user = $this->createMock(User::class);
        $user->expects($this->any())
             ->method('getSettings')
             ->willReturn(new ArrayCollection([]));

        $this->settingRepository->expects($this->once())
                                ->method('createTemporarySetting')
                                ->with($this->identicalTo($user), $this->identicalTo($combinationId))
                                ->willReturn($setting);

        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['readIdFromHeader', 'getFallbackSetting'])
                           ->setConstructorArgs([
                               $this->cookieHelper,
                               $this->serviceManager,
                               $this->settingRepository,
                               $this->userRepository,
                           ])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('readIdFromHeader')
                   ->with($this->identicalTo($request))
                   ->willReturn($combinationId);
        $middleware->expects($this->never())
                   ->method('getFallbackSetting');

        $result = $this->invokeMethod($middleware, 'getCurrentSetting', $request, $user);

        $this->assertSame($setting, $result);
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

        $middleware = new SessionMiddleware(
            $this->cookieHelper,
            $this->serviceManager,
            $this->settingRepository,
            $this->userRepository,
        );
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

        $middleware = new SessionMiddleware(
            $this->cookieHelper,
            $this->serviceManager,
            $this->settingRepository,
            $this->userRepository,
        );
        $result = $this->invokeMethod($middleware, 'readIdFromHeader', $request, $name);

        $this->assertNull($result);
    }

    /**
     * Tests the getFallbackSetting method.
     * @throws ReflectionException
     * @covers ::getFallbackSetting
     */
    public function testGetFallbackSetting(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $setting = $this->createMock(Setting::class);

        $user = $this->createMock(User::class);
        $user->expects($this->once())
             ->method('getLastUsedSetting')
             ->willReturn($setting);

        $this->settingRepository->expects($this->never())
                                ->method('createDefaultSetting');

        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['isInitRoute'])
                           ->setConstructorArgs([
                               $this->cookieHelper,
                               $this->serviceManager,
                               $this->settingRepository,
                               $this->userRepository,
                           ])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('isInitRoute')
                   ->with($this->identicalTo($request))
                   ->willReturn(true);

        $result = $this->invokeMethod($middleware, 'getFallbackSetting', $request, $user);

        $this->assertSame($setting, $result);
    }

    /**
     * Tests the getFallbackSetting method.
     * @throws ReflectionException
     * @covers ::getFallbackSetting
     */
    public function testGetFallbackSettingWithDefaultSetting(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $setting = $this->createMock(Setting::class);

        $settingCollection = $this->createMock(Collection::class);
        $settingCollection->expects($this->once())
                          ->method('add')
                          ->with($this->identicalTo($setting));

        $user = $this->createMock(User::class);
        $user->expects($this->once())
             ->method('getLastUsedSetting')
             ->willReturn(null);
        $user->expects($this->once())
             ->method('getSettings')
             ->willReturn($settingCollection);

        $this->settingRepository->expects($this->once())
                                ->method('createDefaultSetting')
                                ->with($this->identicalTo($user))
                                ->willReturn($setting);

        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['isInitRoute'])
                           ->setConstructorArgs([
                               $this->cookieHelper,
                               $this->serviceManager,
                               $this->settingRepository,
                               $this->userRepository,
                           ])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('isInitRoute')
                   ->with($this->identicalTo($request))
                   ->willReturn(true);

        $result = $this->invokeMethod($middleware, 'getFallbackSetting', $request, $user);

        $this->assertSame($setting, $result);
    }

    /**
     * Tests the getFallbackSetting method.
     * @throws ReflectionException
     * @covers ::getFallbackSetting
     */
    public function testGetFallbackSettingWithException(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $user = $this->createMock(User::class);
        $user->expects($this->never())
             ->method('getLastUsedSetting');

        $this->settingRepository->expects($this->never())
                                ->method('createDefaultSetting');

        $this->expectException(MissingCombinationIdException::class);

        $middleware = $this->getMockBuilder(SessionMiddleware::class)
                           ->onlyMethods(['isInitRoute'])
                           ->setConstructorArgs([
                               $this->cookieHelper,
                               $this->serviceManager,
                               $this->settingRepository,
                               $this->userRepository,
                           ])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('isInitRoute')
                   ->with($this->identicalTo($request))
                   ->willReturn(false);

        $this->invokeMethod($middleware, 'getFallbackSetting', $request, $user);
    }
}
