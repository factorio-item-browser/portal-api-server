<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Helper;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\Mod;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Mod\ModListRequest;
use FactorioItemBrowser\Api\Client\Response\Mod\ModListResponse;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Exception\MissingSettingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
use FactorioItemBrowser\PortalApi\Server\Helper\IconsStyleFetcher;
use FactorioItemBrowser\PortalApi\Server\Helper\SettingHelper;
use FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleData;
use FactorioItemBrowser\PortalApi\Server\Transfer\ModData;
use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
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
     * The mocked current user.
     * @var User&MockObject
     */
    protected $currentUser;

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
        $this->currentUser = $this->createMock(User::class);
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
            $this->currentUser,
            $this->iconsStyleFetcher,
            $this->mapperManager
        );

        $this->assertSame($this->apiClientFactory, $this->extractProperty($helper, 'apiClientFactory'));
        $this->assertSame($this->currentUser, $this->extractProperty($helper, 'currentUser'));
        $this->assertSame($this->iconsStyleFetcher, $this->extractProperty($helper, 'iconsStyleFetcher'));
        $this->assertSame($this->mapperManager, $this->extractProperty($helper, 'mapperManager'));
    }

    /**
     * Tests the findInCurrentUser method.
     * @throws PortalApiServerException
     * @covers ::findInCurrentUser
     */
    public function testFindInCurrentUser(): void
    {
        $combinationId1 = '17060d93-bc42-4c04-abbf-f7c7ed7b7ad3';
        $combination1 = new Combination();
        $combination1->setId(Uuid::fromString($combinationId1));
        $setting1 = new Setting();
        $setting1->setCombination($combination1);

        $combinationId2 = '259f8986-37b0-4e07-a5c9-235e066fb232';
        $combination2 = new Combination();
        $combination2->setId(Uuid::fromString($combinationId2));
        $setting2 = new Setting();
        $setting2->setCombination($combination2);

        $combinationId = Uuid::fromString($combinationId2);
        $expectedResult = $setting2;

        $this->currentUser->expects($this->once())
                          ->method('getSettings')
                          ->willReturn(new ArrayCollection([$setting1, $setting2]));

        $helper = new SettingHelper(
            $this->apiClientFactory,
            $this->currentUser,
            $this->iconsStyleFetcher,
            $this->mapperManager
        );
        $result = $helper->findInCurrentUser($combinationId);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the findInCurrentUser method.
     * @throws PortalApiServerException
     * @covers ::findInCurrentUser
     */
    public function testFindInCurrentUserWithoutMatch(): void
    {
        $combinationId1 = '17060d93-bc42-4c04-abbf-f7c7ed7b7ad3';
        $combination1 = new Combination();
        $combination1->setId(Uuid::fromString($combinationId1));
        $setting1 = new Setting();
        $setting1->setCombination($combination1);

        $combinationId2 = '259f8986-37b0-4e07-a5c9-235e066fb232';
        $combination2 = new Combination();
        $combination2->setId(Uuid::fromString($combinationId2));
        $setting2 = new Setting();
        $setting2->setCombination($combination2);

        $combinationId = Uuid::fromString('f123ae9b-e7fd-4354-ba54-36169cf3db35');

        $this->currentUser->expects($this->once())
                          ->method('getSettings')
                          ->willReturn(new ArrayCollection([$setting1, $setting2]));

        $this->expectException(MissingSettingException::class);

        $helper = new SettingHelper(
            $this->apiClientFactory,
            $this->currentUser,
            $this->iconsStyleFetcher,
            $this->mapperManager
        );
        $helper->findInCurrentUser($combinationId);
    }

    /**
     * Tests the createSettingMeta method.
     * @throws PortalApiServerException
     * @covers ::createSettingMeta
     */
    public function testCreateSettingMeta(): void
    {
        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($setting), $this->isInstanceOf(SettingMetaData::class));

        $helper = new SettingHelper(
            $this->apiClientFactory,
            $this->currentUser,
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
        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($setting), $this->isInstanceOf(SettingMetaData::class))
                            ->willThrowException($this->createMock(MapperException::class));

        $this->expectException(MappingException::class);

        $helper = new SettingHelper(
            $this->apiClientFactory,
            $this->currentUser,
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
        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        /* @var Mod&MockObject $mod1 */
        $mod1 = $this->createMock(Mod::class);
        /* @var Mod&MockObject $mod2 */
        $mod2 = $this->createMock(Mod::class);
        /* @var ModData&MockObject $modData1 */
        $modData1 = $this->createMock(ModData::class);
        /* @var ModData&MockObject $modData2 */
        $modData2 = $this->createMock(ModData::class);
        /* @var NamesByTypes&MockObject $modNames */
        $modNames = $this->createMock(NamesByTypes::class);
        /* @var IconsStyleData&MockObject $iconsStyleData */
        $iconsStyleData = $this->createMock(IconsStyleData::class);

        $expectedModListRequest = new ModListRequest();
        $expectedResult = new SettingDetailsData();
        $expectedResult->setMods([$modData1, $modData2])
                       ->setModIconsStyle($iconsStyleData);

        /* @var ModListResponse&MockObject $modListResponse */
        $modListResponse = $this->createMock(ModListResponse::class);
        $modListResponse->expects($this->once())
                        ->method('getMods')
                        ->willReturn([$mod1, $mod2]);

        /* @var ApiClientInterface&MockObject $apiClient */
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

        /* @var SettingHelper&MockObject $helper */
        $helper = $this->getMockBuilder(SettingHelper::class)
                       ->onlyMethods(['extractModNames', 'mapMod'])
                       ->setConstructorArgs([
                           $this->apiClientFactory,
                           $this->currentUser,
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
        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);

        $expectedModListRequest = new ModListRequest();

        /* @var ApiClientInterface&MockObject $apiClient */
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

        /* @var SettingHelper&MockObject $helper */
        $helper = $this->getMockBuilder(SettingHelper::class)
                       ->onlyMethods(['extractModNames', 'mapMod'])
                       ->setConstructorArgs([
                           $this->apiClientFactory,
                           $this->currentUser,
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
        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        /* @var NamesByTypes&MockObject $modNames */
        $modNames = $this->createMock(NamesByTypes::class);

        $expectedModListRequest = new ModListRequest();

        /* @var ApiClientInterface&MockObject $apiClient */
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

        /* @var SettingHelper&MockObject $helper */
        $helper = $this->getMockBuilder(SettingHelper::class)
                       ->onlyMethods(['extractModNames', 'mapMod'])
                       ->setConstructorArgs([
                           $this->apiClientFactory,
                           $this->currentUser,
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
            $this->currentUser,
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
        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($mod), $this->isInstanceOf(ModData::class));

        $helper = new SettingHelper(
            $this->apiClientFactory,
            $this->currentUser,
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
        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($mod), $this->isInstanceOf(ModData::class))
                            ->willThrowException($this->createMock(MapperException::class));

        $this->expectException(MappingException::class);

        $helper = new SettingHelper(
            $this->apiClientFactory,
            $this->currentUser,
            $this->iconsStyleFetcher,
            $this->mapperManager
        );
        $this->invokeMethod($helper, 'mapMod', $mod);
    }

    /**
     * Tests the calculateHash method.
     * @covers ::calculateHash
     */
    public function testCalculateHash(): void
    {
        $settingId = Uuid::fromString('03c44592-9276-43e4-a5ff-38b20cce8d7f');
        $combinationId = Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76');
        $exportTime = new DateTime('2038-01-19 03:14:07');

        $expectedResult = md5((string) json_encode([
            '03c44592-9276-43e4-a5ff-38b20cce8d7f',
            '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76',
            '2038-01-19T03:14:07+00:00',
            'abc',
            'def',
        ]));

        $combination = new Combination();
        $combination->setId($combinationId)
                    ->setExportTime($exportTime);

        $setting = new Setting();
        $setting->setId($settingId)
                ->setCombination($combination)
                ->setLocale('abc')
                ->setRecipeMode('def');

        $helper = new SettingHelper(
            $this->apiClientFactory,
            $this->currentUser,
            $this->iconsStyleFetcher,
            $this->mapperManager
        );
        $result = $helper->calculateHash($setting);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the calculateHash method.
     * @covers ::calculateHash
     */
    public function testCalculateHashWithoutExportTime(): void
    {
        $settingId = Uuid::fromString('03c44592-9276-43e4-a5ff-38b20cce8d7f');
        $combinationId = Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76');

        $expectedResult = md5((string) json_encode([
            '03c44592-9276-43e4-a5ff-38b20cce8d7f',
            '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76',
            '',
            'abc',
            'def',
        ]));

        $combination = new Combination();
        $combination->setId($combinationId);

        $setting = new Setting();
        $setting->setId($settingId)
                ->setCombination($combination)
                ->setLocale('abc')
                ->setRecipeMode('def');

        $helper = new SettingHelper(
            $this->apiClientFactory,
            $this->currentUser,
            $this->iconsStyleFetcher,
            $this->mapperManager
        );
        $result = $helper->calculateHash($setting);

        $this->assertSame($expectedResult, $result);
    }
}
