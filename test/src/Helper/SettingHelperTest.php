<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Helper;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\Mod;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Mod\ModListRequest;
use FactorioItemBrowser\Api\Client\Response\Mod\ModListResponse;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Helper\IconsStyleFetcher;
use FactorioItemBrowser\PortalApi\Server\Helper\SettingHelper;
use FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleData;
use FactorioItemBrowser\PortalApi\Server\Transfer\ModData;
use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the SettingHelper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Helper\SettingHelper
 */
class SettingHelperTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked api client factory.
     * @var ApiClientFactory&MockObject
     */
    protected $apiClientFactory;

    /**
     * The mocked icons style fetcher.
     * @var IconsStyleFetcher&MockObject
     */
    protected $iconsStyleFetcher;

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
        $this->iconsStyleFetcher = $this->createMock(IconsStyleFetcher::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $helper = new SettingHelper(
            $this->apiClientFactory,
            $this->iconsStyleFetcher,
            $this->mapperManager
        );

        $this->assertSame($this->apiClientFactory, $this->extractProperty($helper, 'apiClientFactory'));
        $this->assertSame($this->iconsStyleFetcher, $this->extractProperty($helper, 'iconsStyleFetcher'));
        $this->assertSame($this->mapperManager, $this->extractProperty($helper, 'mapperManager'));
    }

    /**
     * Tests the createSettingMeta method.
     * @throws PortalApiServerException
     * @covers ::createSettingMeta
     */
    public function testCreateSettingMeta(): void
    {
        $setting = $this->createMock(Setting::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($setting), $this->isInstanceOf(SettingMetaData::class));

        $helper = new SettingHelper(
            $this->apiClientFactory,
            $this->iconsStyleFetcher,
            $this->mapperManager
        );
        $helper->createSettingMeta($setting);
    }

    /**
     * Tests the createSettingMeta method.
     * @throws PortalApiServerException
     * @covers ::createSettingMeta
     */
    public function testCreateSettingMetaWithException(): void
    {
        $setting = $this->createMock(Setting::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($setting), $this->isInstanceOf(SettingMetaData::class))
                            ->willThrowException($this->createMock(MapperException::class));

        $this->expectException(MappingException::class);

        $helper = new SettingHelper(
            $this->apiClientFactory,
            $this->iconsStyleFetcher,
            $this->mapperManager
        );
        $helper->createSettingMeta($setting);
    }

    /**
     * Tests the createSettingDetails method.
     * @throws PortalApiServerException
     * @covers ::createSettingDetails
     */
    public function testCreateSettingDetails(): void
    {
        $setting = $this->createMock(Setting::class);
        $mod1 = $this->createMock(Mod::class);
        $mod2 = $this->createMock(Mod::class);
        $modData1 = $this->createMock(ModData::class);
        $modData2 = $this->createMock(ModData::class);
        $modNames = $this->createMock(NamesByTypes::class);
        $iconsStyleData = $this->createMock(IconsStyleData::class);

        $expectedModListRequest = new ModListRequest();
        $expectedResult = new SettingDetailsData();
        $expectedResult->setMods([$modData1, $modData2])
                       ->setModIconsStyle($iconsStyleData);

        $modListResponse = $this->createMock(ModListResponse::class);
        $modListResponse->expects($this->once())
                        ->method('getMods')
                        ->willReturn([$mod1, $mod2]);

        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
                  ->method('sendRequest')
                  ->with($this->equalTo($expectedModListRequest));
        $apiClient->expects($this->once())
                  ->method('fetchResponse')
                  ->with($this->equalTo($expectedModListRequest))
                  ->willReturn($modListResponse);

        $this->apiClientFactory->expects($this->once())
                               ->method('create')
                               ->with($this->identicalTo($setting))
                               ->willReturn($apiClient);

        $this->iconsStyleFetcher->expects($this->once())
                                ->method('request')
                                ->with($this->identicalTo($setting), $this->identicalTo($modNames));
        $this->iconsStyleFetcher->expects($this->once())
                                ->method('process')
                                ->willReturn($iconsStyleData);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($setting), $this->isInstanceOf(SettingDetailsData::class));

        $helper = $this->getMockBuilder(SettingHelper::class)
                       ->onlyMethods(['extractModNames', 'mapMod'])
                       ->setConstructorArgs([
                           $this->apiClientFactory,
                           $this->iconsStyleFetcher,
                           $this->mapperManager,
                       ])
                       ->getMock();
        $helper->expects($this->once())
               ->method('extractModNames')
               ->with($this->identicalTo($setting))
               ->willReturn($modNames);
        $helper->expects($this->exactly(2))
               ->method('mapMod')
               ->withConsecutive(
                   [$this->identicalTo($mod1)],
                   [$this->identicalTo($mod2)]
               )
               ->willReturnOnConsecutiveCalls(
                   $modData1,
                   $modData2
               );

        $result = $helper->createSettingDetails($setting);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the createSettingDetails method.
     * @throws PortalApiServerException
     * @covers ::createSettingDetails
     */
    public function testCreateSettingDetailsWithApiException(): void
    {
        $setting = $this->createMock(Setting::class);

        $expectedModListRequest = new ModListRequest();

        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
                  ->method('sendRequest')
                  ->with($this->equalTo($expectedModListRequest))
                  ->willThrowException($this->createMock(ApiClientException::class));
        $apiClient->expects($this->never())
                  ->method('fetchResponse');

        $this->apiClientFactory->expects($this->once())
                               ->method('create')
                               ->with($this->identicalTo($setting))
                               ->willReturn($apiClient);

        $this->iconsStyleFetcher->expects($this->never())
                                ->method('request');
        $this->iconsStyleFetcher->expects($this->never())
                                ->method('process');

        $this->mapperManager->expects($this->never())
                            ->method('map');

        $this->expectException(FailedApiRequestException::class);

        $helper = $this->getMockBuilder(SettingHelper::class)
                       ->onlyMethods(['extractModNames', 'mapMod'])
                       ->setConstructorArgs([
                           $this->apiClientFactory,
                           $this->iconsStyleFetcher,
                           $this->mapperManager,
                       ])
                       ->getMock();
        $helper->expects($this->never())
               ->method('extractModNames');
        $helper->expects($this->never())
               ->method('mapMod');

        $helper->createSettingDetails($setting);
    }

    /**
     * Tests the createSettingDetails method.
     * @throws PortalApiServerException
     * @covers ::createSettingDetails
     */
    public function testCreateSettingDetailsWithMapperException(): void
    {
        $setting = $this->createMock(Setting::class);
        $modNames = $this->createMock(NamesByTypes::class);

        $expectedModListRequest = new ModListRequest();

        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
                  ->method('sendRequest')
                  ->with($this->equalTo($expectedModListRequest));
        $apiClient->expects($this->never())
                  ->method('fetchResponse');

        $this->apiClientFactory->expects($this->once())
                               ->method('create')
                               ->with($this->identicalTo($setting))
                               ->willReturn($apiClient);

        $this->iconsStyleFetcher->expects($this->once())
                                ->method('request')
                                ->with($this->identicalTo($setting), $this->identicalTo($modNames));
        $this->iconsStyleFetcher->expects($this->never())
                                ->method('process');

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($setting), $this->isInstanceOf(SettingDetailsData::class))
                            ->willThrowException($this->createMock(MapperException::class));

        $this->expectException(MappingException::class);

        $helper = $this->getMockBuilder(SettingHelper::class)
                       ->onlyMethods(['extractModNames', 'mapMod'])
                       ->setConstructorArgs([
                           $this->apiClientFactory,
                           $this->iconsStyleFetcher,
                           $this->mapperManager,
                       ])
                       ->getMock();
        $helper->expects($this->once())
               ->method('extractModNames')
               ->with($this->identicalTo($setting))
               ->willReturn($modNames);
        $helper->expects($this->never())
               ->method('mapMod');

        $helper->createSettingDetails($setting);
    }


    /**
     * Tests the createSettingDetailsWithoutMods method.
     * @throws PortalApiServerException
     * @covers ::createSettingDetailsWithoutMods
     */
    public function testCreateSettingDetailsWithoutMods(): void
    {
        $setting = $this->createMock(Setting::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($setting), $this->isInstanceOf(SettingDetailsData::class));

        $helper = new SettingHelper(
            $this->apiClientFactory,
            $this->iconsStyleFetcher,
            $this->mapperManager
        );
        $helper->createSettingDetailsWithoutMods($setting);
    }

    /**
     * Tests the createSettingDetailsWithoutMods method.
     * @throws PortalApiServerException
     * @covers ::createSettingDetailsWithoutMods
     */
    public function testCreateSettingMetaWithoutModsWithException(): void
    {
        $setting = $this->createMock(Setting::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($setting), $this->isInstanceOf(SettingDetailsData::class))
                            ->willThrowException($this->createMock(MapperException::class));

        $this->expectException(MappingException::class);

        $helper = new SettingHelper(
            $this->apiClientFactory,
            $this->iconsStyleFetcher,
            $this->mapperManager
        );
        $helper->createSettingDetailsWithoutMods($setting);
    }

    /**
     * Tests the extractModNames method.
     * @throws ReflectionException
     * @covers ::extractModNames
     */
    public function testExtractModNames(): void
    {
        $combination = new Combination();
        $combination->setModNames(['abc', 'def']);

        $setting = new Setting();
        $setting->setCombination($combination);

        $expectedResult = new NamesByTypes();
        $expectedResult->setValues([
            EntityType::MOD => ['abc', 'def'],
        ]);

        $helper = new SettingHelper(
            $this->apiClientFactory,
            $this->iconsStyleFetcher,
            $this->mapperManager
        );
        $result = $this->invokeMethod($helper, 'extractModNames', $setting);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the mapMod method.
     * @throws ReflectionException
     * @covers ::mapMod
     */
    public function testMapMod(): void
    {
        $mod = $this->createMock(Mod::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($mod), $this->isInstanceOf(ModData::class));

        $helper = new SettingHelper(
            $this->apiClientFactory,
            $this->iconsStyleFetcher,
            $this->mapperManager
        );
        $this->invokeMethod($helper, 'mapMod', $mod);
    }

    /**
     * Tests the mapMod method.
     * @throws ReflectionException
     * @covers ::mapMod
     */
    public function testMapModWithException(): void
    {
        $mod = $this->createMock(Mod::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($mod), $this->isInstanceOf(ModData::class))
                            ->willThrowException($this->createMock(MapperException::class));

        $this->expectException(MappingException::class);

        $helper = new SettingHelper(
            $this->apiClientFactory,
            $this->iconsStyleFetcher,
            $this->mapperManager
        );
        $this->invokeMethod($helper, 'mapMod', $mod);
    }
}
