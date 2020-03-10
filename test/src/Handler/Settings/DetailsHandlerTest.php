<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Settings;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\Mod;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
use FactorioItemBrowser\PortalApi\Server\Handler\Settings\DetailsHandler;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ReflectionException;

/**
 * The PHPUnit test of the DetailsHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Settings\DetailsHandler
 */
class DetailsHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked api client factory.
     * @var ApiClientFactory&MockObject
     */
    protected $apiClientFactory;

    /**
     * The mocked current user.
     * @var User&MockObject
     */
    protected $currentUser;

    /**
     * The mocked mapper manager.
     * @var MapperManagerInterface&MockObject
     */
    protected $mapperManager;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClientFactory = $this->createMock(ApiClientFactory::class);
        $this->currentUser = $this->createMock(User::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new DetailsHandler($this->apiClientFactory, $this->currentUser, $this->mapperManager);

        $this->assertSame($this->apiClientFactory, $this->extractProperty($handler, 'apiClientFactory'));
        $this->assertSame($this->currentUser, $this->extractProperty($handler, 'currentUser'));
        $this->assertSame($this->mapperManager, $this->extractProperty($handler, 'mapperManager'));
    }

    /**
     * Tests the handle method.
     * @throws PortalApiServerException
     * @covers ::handle
     */
    public function testHandle(): void
    {
        $settingIdString = 'a20ef5d4-59bf-48aa-9b72-2e5ddb2f2995';
        $settingId = Uuid::fromString($settingIdString);

        $mods = [
            $this->createMock(Mod::class),
            $this->createMock(Mod::class),
        ];

        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        /* @var SettingDetailsData&MockObject $settingDetails */
        $settingDetails = $this->createMock(SettingDetailsData::class);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo('setting-id'), $this->identicalTo(''))
                ->willReturn($settingIdString);

        $this->apiClientFactory->expects($this->once())
                               ->method('create')
                               ->with($this->identicalTo($setting))
                               ->willReturn($apiClient);

        /* @var DetailsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(DetailsHandler::class)
                        ->onlyMethods(['findSetting', 'fetchMods', 'mapSettingDetails'])
                        ->setConstructorArgs([$this->apiClientFactory, $this->currentUser, $this->mapperManager])
                        ->getMock();
        $handler->expects($this->once())
                ->method('findSetting')
                ->with($this->equalTo($settingId))
                ->willReturn($setting);
        $handler->expects($this->once())
                ->method('fetchMods')
                ->with($this->identicalTo($apiClient))
                ->willReturn($mods);
        $handler->expects($this->once())
                ->method('mapSettingDetails')
                ->with($this->identicalTo($setting), $this->identicalTo($mods))
                ->willReturn($settingDetails);

        /* @var TransferResponse $result */
        $result = $handler->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        $this->assertSame($settingDetails, $result->getTransfer());
    }

    /**
     * Tests the findSetting method.
     * @throws ReflectionException
     * @covers ::findSetting
     */
    public function testFindSetting(): void
    {
        /* @var UuidInterface&MockObject $settingId */
        $settingId = $this->createMock(UuidInterface::class);

        /* @var UuidInterface&MockObject $uuid1 */
        $uuid1 = $this->createMock(UuidInterface::class);
        $uuid1->expects($this->once())
              ->method('compareTo')
              ->with($this->identicalTo($settingId))
              ->willReturn(1);

        /* @var UuidInterface&MockObject $uuid2 */
        $uuid2 = $this->createMock(UuidInterface::class);
        $uuid2->expects($this->once())
              ->method('compareTo')
              ->with($this->identicalTo($settingId))
              ->willReturn(0);

        /* @var Setting&MockObject $setting1 */
        $setting1 = $this->createMock(Setting::class);
        $setting1->expects($this->once())
                 ->method('getId')
                 ->willReturn($uuid1);

        /* @var Setting&MockObject $setting2 */
        $setting2 = $this->createMock(Setting::class);
        $setting2->expects($this->once())
                 ->method('getId')
                 ->willReturn($uuid2);

        $this->currentUser->expects($this->once())
                          ->method('getSettings')
                          ->willReturn(new ArrayCollection([$setting1, $setting2]));

        $handler = new DetailsHandler($this->apiClientFactory, $this->currentUser, $this->mapperManager);
        $result = $this->invokeMethod($handler, 'findSetting', $settingId);

        $this->assertSame($setting2, $result);
    }

    /**
     * Tests the findSetting method.
     * @throws ReflectionException
     * @covers ::findSetting
     */
    public function testFindSettingWithoutMatch(): void
    {
        /* @var UuidInterface&MockObject $settingId */
        $settingId = $this->createMock(UuidInterface::class);
        $settingId->expects($this->once())
                  ->method('toString')
                  ->willReturn('abc');

        /* @var UuidInterface&MockObject $uuid1 */
        $uuid1 = $this->createMock(UuidInterface::class);
        $uuid1->expects($this->once())
              ->method('compareTo')
              ->with($this->identicalTo($settingId))
              ->willReturn(1);

        /* @var UuidInterface&MockObject $uuid2 */
        $uuid2 = $this->createMock(UuidInterface::class);
        $uuid2->expects($this->once())
              ->method('compareTo')
              ->with($this->identicalTo($settingId))
              ->willReturn(-1);

        /* @var Setting&MockObject $setting1 */
        $setting1 = $this->createMock(Setting::class);
        $setting1->expects($this->once())
                 ->method('getId')
                 ->willReturn($uuid1);

        /* @var Setting&MockObject $setting2 */
        $setting2 = $this->createMock(Setting::class);
        $setting2->expects($this->once())
                 ->method('getId')
                 ->willReturn($uuid2);

        $this->currentUser->expects($this->once())
                          ->method('getSettings')
                          ->willReturn(new ArrayCollection([$setting1, $setting2]));

        $this->expectException(UnknownEntityException::class);

        $handler = new DetailsHandler($this->apiClientFactory, $this->currentUser, $this->mapperManager);
        $this->invokeMethod($handler, 'findSetting', $settingId);
    }
}
