<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Helper;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Request\Mod\ModListRequest;
use FactorioItemBrowser\Api\Client\Response\Mod\ModListResponse;
use FactorioItemBrowser\Api\Client\Transfer\Mod;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Helper\IconsStyleFetcher;
use FactorioItemBrowser\PortalApi\Server\Helper\SettingHelper;
use FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleData;
use FactorioItemBrowser\PortalApi\Server\Transfer\ModData;
use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the SettingHelper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Helper\SettingHelper
 */
class SettingHelperTest extends TestCase
{
    /** @var ClientInterface&MockObject */
    private ClientInterface $apiClient;
    /** @var IconsStyleFetcher&MockObject */
    private IconsStyleFetcher $iconsStyleFetcher;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;

    protected function setUp(): void
    {
        $this->apiClient = $this->createMock(ClientInterface::class);
        $this->iconsStyleFetcher = $this->createMock(IconsStyleFetcher::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return SettingHelper&MockObject
     */
    private function createInstance(array $mockedMethods = []): SettingHelper
    {
        return $this->getMockBuilder(SettingHelper::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->apiClient,
                        $this->iconsStyleFetcher,
                        $this->mapperManager,
                    ])
                    ->getMock();
    }

    public function testCreateSettingMeta(): void
    {
        $setting = $this->createMock(Setting::class);
        $settingData = $this->createMock(SettingMetaData::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($setting), $this->isInstanceOf(SettingMetaData::class))
                            ->willReturn($settingData);

        $instance = $this->createInstance();
        $result = $instance->createSettingMeta($setting);

        $this->assertSame($settingData, $result);
    }

    /**
     * @return array<mixed>
     */
    public function provideHandleCreateSettingDetails(): array
    {
        return [
            ['78de8fa6-424b-479e-99c2-bb719eff1e0d', true, '78de8fa6-424b-479e-99c2-bb719eff1e0d'],
            ['78de8fa6-424b-479e-99c2-bb719eff1e0d', false, '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76'],
        ];
    }

    /**
     * @param string $combinationId
     * @param bool $hasData
     * @param string $expectedCombinationId
     * @throws PortalApiServerException
     * @dataProvider provideHandleCreateSettingDetails
     */
    public function testHandleCreateSettingDetails(
        string $combinationId,
        bool $hasData,
        string $expectedCombinationId
    ): void {
        $combination = new Combination();
        $combination->setId(Uuid::fromString($combinationId))
                    ->setModNames(['abc', 'def']);

        $setting = new Setting();
        $setting->setCombination($combination)
                ->setLocale('foo')
                ->setHasData($hasData);

        $modNames = new NamesByTypes();
        $modNames->values = ['mod' => ['abc', 'def']];
        $expectedApiRequest = new ModListRequest();
        $expectedApiRequest->combinationId = $expectedCombinationId;
        $expectedApiRequest->locale = 'foo';

        $mod1 = $this->createMock(Mod::class);
        $mod2 = $this->createMock(Mod::class);
        $modData1 = $this->createMock(ModData::class);
        $modData2 = $this->createMock(ModData::class);

        $apiResponse = new ModListResponse();
        $apiResponse->mods = [$mod1, $mod2];

        $iconsStyle = new IconsStyleData();
        $iconsStyle->style = 'bar';
        $iconsPromise = $this->createMock(PromiseInterface::class);
        $settingData = new SettingDetailsData();
        $settingData->locale = 'foo';
        $expectedSettingData = new SettingDetailsData();
        $expectedSettingData->locale = 'foo';
        $expectedSettingData->mods = [$modData1, $modData2];
        $expectedSettingData->modIconsStyle = $iconsStyle;

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willReturn(new FulfilledPromise($apiResponse));

        $this->mapperManager->expects($this->exactly(3))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($setting), $this->isInstanceOf(SettingDetailsData::class)],
                                [$this->identicalTo($mod1), $this->isInstanceOf(ModData::class)],
                                [$this->identicalTo($mod2), $this->isInstanceOf(ModData::class)],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $settingData,
                                $modData1,
                                $modData2
                            );

        $this->iconsStyleFetcher->expects($this->once())
                                ->method('request')
                                ->with($this->identicalTo($setting), $this->equalTo($modNames))
                                ->willReturn($iconsPromise);
        $this->iconsStyleFetcher->expects($this->once())
                                ->method('process')
                                ->with($this->identicalTo($iconsPromise))
                                ->willReturn($iconsStyle);
        $this->iconsStyleFetcher->expects($this->once())
                                ->method('addMissingEntities')
                                ->with($this->identicalTo($iconsStyle->processedEntities), $this->equalTo($modNames));

        $instance = $this->createInstance();
        $result = $instance->createSettingDetails($setting);

        $this->assertEquals($expectedSettingData, $result);
    }

    /**
     * @throws PortalApiServerException
     */
    public function testCreateSettingDetailsWithApiException(): void
    {
        $combination = new Combination();
        $combination->setId(Uuid::fromString('78de8fa6-424b-479e-99c2-bb719eff1e0d'))
                    ->setModNames(['abc', 'def']);

        $setting = new Setting();
        $setting->setCombination($combination)
                ->setLocale('foo')
                ->setHasData(true);

        $modNames = new NamesByTypes();
        $modNames->values = ['mod' => ['abc', 'def']];
        $expectedApiRequest = new ModListRequest();
        $expectedApiRequest->combinationId = '78de8fa6-424b-479e-99c2-bb719eff1e0d';
        $expectedApiRequest->locale = 'foo';

        $iconsPromise = $this->createMock(PromiseInterface::class);
        $settingData = new SettingDetailsData();
        $settingData->locale = 'foo';

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willThrowException($this->createMock(ClientException::class));

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($setting), $this->isInstanceOf(SettingDetailsData::class))
                            ->willReturn($settingData);

        $this->iconsStyleFetcher->expects($this->once())
                                ->method('request')
                                ->with($this->identicalTo($setting), $this->equalTo($modNames))
                                ->willReturn($iconsPromise);

        $this->expectException(FailedApiRequestException::class);

        $instance = $this->createInstance();
        $instance->createSettingDetails($setting);
    }

    public function testCreateSettingDetailsWithoutMods(): void
    {
        $setting = $this->createMock(Setting::class);
        $settingData = $this->createMock(SettingDetailsData::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($setting), $this->isInstanceOf(SettingDetailsData::class))
                            ->willReturn($settingData);

        $instance = $this->createInstance();
        $result = $instance->createSettingDetailsWithoutMods($setting);

        $this->assertSame($settingData, $result);
    }
}
