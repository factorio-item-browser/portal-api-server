<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Settings;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\Mod;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Mod\ModListRequest;
use FactorioItemBrowser\Api\Client\Response\Mod\ModListResponse;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Handler\Settings\AbstractSettingsHandler;
use FactorioItemBrowser\PortalApi\Server\Transfer\ModData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the AbstractSettingsHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Settings\AbstractSettingsHandler
 */
class AbstractSettingsHandlerTest extends TestCase
{
    use ReflectionTrait;

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

        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var AbstractSettingsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractSettingsHandler::class)
                        ->setConstructorArgs([$this->mapperManager])
                        ->getMockForAbstractClass();

        $this->assertSame($this->mapperManager, $this->extractProperty($handler, 'mapperManager'));
    }

    /**
     * Tests the fetchMods method.
     * @throws ReflectionException
     * @covers ::fetchMods
     */
    public function testFetchMods(): void
    {
        $mods = [
            $this->createMock(Mod::class),
            $this->createMock(Mod::class),
        ];

        /* @var ModListResponse&MockObject $response */
        $response = $this->createMock(ModListResponse::class);
        $response->expects($this->once())
                 ->method('getMods')
                 ->willReturn($mods);

        $expectedRequest = new ModListRequest();

        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
                  ->method('fetchResponse')
                  ->with($this->equalTo($expectedRequest))
                  ->willReturn($response);

        /* @var AbstractSettingsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractSettingsHandler::class)
                        ->setConstructorArgs([$this->mapperManager])
                        ->getMockForAbstractClass();

        $result = $this->invokeMethod($handler, 'fetchMods', $apiClient);
        $this->assertSame($mods, $result);
    }

    /**
     * Tests the fetchMods method.
     * @throws ReflectionException
     * @covers ::fetchMods
     */
    public function testFetchModsWithException(): void
    {
        $expectedRequest = new ModListRequest();

        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
                  ->method('fetchResponse')
                  ->with($this->equalTo($expectedRequest))
                  ->willThrowException($this->createMock(ApiClientException::class));

        $this->expectException(FailedApiRequestException::class);

        /* @var AbstractSettingsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractSettingsHandler::class)
                        ->setConstructorArgs([$this->mapperManager])
                        ->getMockForAbstractClass();

        $this->invokeMethod($handler, 'fetchMods', $apiClient);
    }

    /**
     * Tests the mapSettingMeta method.
     * @throws ReflectionException
     * @covers ::mapSettingMeta
     */
    public function testMapSettingMeta(): void
    {
        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($setting), $this->isInstanceOf(SettingMetaData::class));

        /* @var AbstractSettingsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractSettingsHandler::class)
                        ->setConstructorArgs([$this->mapperManager])
                        ->getMockForAbstractClass();

        $this->invokeMethod($handler, 'mapSettingMeta', $setting);
    }

    /**
     * Tests the mapSettingMeta method.
     * @throws ReflectionException
     * @covers ::mapSettingMeta
     */
    public function testMapSettingMetaWithException(): void
    {
        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($setting), $this->isInstanceOf(SettingMetaData::class))
                            ->willThrowException($this->createMock(MapperException::class));

        $this->expectException(MappingException::class);

        /* @var AbstractSettingsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractSettingsHandler::class)
                        ->setConstructorArgs([$this->mapperManager])
                        ->getMockForAbstractClass();

        $this->invokeMethod($handler, 'mapSettingMeta', $setting);
    }

    /**
     * Tests the mapSettingDetails method.
     * @throws ReflectionException
     * @covers ::mapSettingDetails
     */
    public function testMapSettingDetails(): void
    {
        /* @var Mod&MockObject $mod1 */
        $mod1 = $this->createMock(Mod::class);
        /* @var Mod&MockObject $mod2 */
        $mod2 = $this->createMock(Mod::class);
        /* @var ModData&MockObject $modData1 */
        $modData1 = $this->createMock(ModData::class);
        /* @var ModData&MockObject $modData2 */
        $modData2 = $this->createMock(ModData::class);

        $mods = [$mod1, $mod2];
        $expectedModData = [$modData1, $modData2];

        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($setting), $this->isInstanceOf(SettingDetailsData::class));

        /* @var AbstractSettingsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractSettingsHandler::class)
                        ->onlyMethods(['mapMod'])
                        ->setConstructorArgs([$this->mapperManager])
                        ->getMockForAbstractClass();
        $handler->expects($this->exactly(2))
                ->method('mapMod')
                ->withConsecutive(
                    [$this->identicalTo($mod1)],
                    [$this->identicalTo($mod2)]
                )
                ->willReturnOnConsecutiveCalls(
                    $modData1,
                    $modData2
                );

        /* @var SettingDetailsData $result */
        $result = $this->invokeMethod($handler, 'mapSettingDetails', $setting, $mods);

        $this->assertSame($expectedModData, $result->getMods());
    }

    /**
     * Tests the mapSettingDetails method.
     * @throws ReflectionException
     * @covers ::mapSettingDetails
     */
    public function testMapSettingDetailsWithException(): void
    {
        /* @var Mod&MockObject $mod1 */
        $mod1 = $this->createMock(Mod::class);
        /* @var Mod&MockObject $mod2 */
        $mod2 = $this->createMock(Mod::class);

        $mods = [$mod1, $mod2];

        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($setting), $this->isInstanceOf(SettingDetailsData::class))
                            ->willThrowException($this->createMock(MapperException::class));

        $this->expectException(MappingException::class);

        /* @var AbstractSettingsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractSettingsHandler::class)
                        ->onlyMethods(['mapMod'])
                        ->setConstructorArgs([$this->mapperManager])
                        ->getMockForAbstractClass();
        $handler->expects($this->never())
                ->method('mapMod');

        $this->invokeMethod($handler, 'mapSettingDetails', $setting, $mods);
    }

    /**
     * Tests the mapMod method.
     * @throws ReflectionException
     * @covers ::mapMod
     */
    public function testMapMod(): void
    {
        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($mod), $this->isInstanceOf(ModData::class));

        /* @var AbstractSettingsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractSettingsHandler::class)
                        ->setConstructorArgs([$this->mapperManager])
                        ->getMockForAbstractClass();

        $this->invokeMethod($handler, 'mapMod', $mod);
    }

    /**
     * Tests the mapMod method.
     * @throws ReflectionException
     * @covers ::mapMod
     */
    public function testMapModWithException(): void
    {
        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($mod), $this->isInstanceOf(ModData::class))
                            ->willThrowException($this->createMock(MapperException::class));

        $this->expectException(MappingException::class);

        /* @var AbstractSettingsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractSettingsHandler::class)
                        ->setConstructorArgs([$this->mapperManager])
                        ->getMockForAbstractClass();

        $this->invokeMethod($handler, 'mapMod', $mod);
    }
}
