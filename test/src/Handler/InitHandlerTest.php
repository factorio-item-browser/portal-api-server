<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler;

use BluePsyduck\TestHelper\ReflectionTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Combination\CombinationStatusRequest;
use FactorioItemBrowser\Api\Client\Response\Combination\CombinationStatusResponse;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
use FactorioItemBrowser\PortalApi\Server\Constant\CombinationStatus;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Handler\InitHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
use FactorioItemBrowser\PortalApi\Server\Helper\SettingHelper;
use FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\InitData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

/**
 * The PHPUnit test of the InitHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\InitHandler
 */
class InitHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked api client factory.
     * @var ApiClientFactory&MockObject
     */
    protected $apiClientFactory;

    /**
     * The mocked combination helper.
     * @var CombinationHelper&MockObject
     */
    protected $combinationHelper;

    /**
     * The mocked current setting.
     * @var Setting&MockObject
     */
    protected $currentSetting;

    /**
     * The mocked setting helper.
     * @var SettingHelper&MockObject
     */
    protected $settingHelper;

    /**
     * The mocked sidebar entities helper.
     * @var SidebarEntitiesHelper&MockObject
     */
    protected $sidebarEntitiesHelper;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClientFactory = $this->createMock(ApiClientFactory::class);
        $this->combinationHelper = $this->createMock(CombinationHelper::class);
        $this->currentSetting = $this->createMock(Setting::class);
        $this->settingHelper = $this->createMock(SettingHelper::class);
        $this->sidebarEntitiesHelper = $this->createMock(SidebarEntitiesHelper::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $scriptVersion = 'abc';

        $handler = new InitHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentSetting,
            $this->settingHelper,
            $this->sidebarEntitiesHelper,
            $scriptVersion
        );

        $this->assertSame($this->apiClientFactory, $this->extractProperty($handler, 'apiClientFactory'));
        $this->assertSame($this->combinationHelper, $this->extractProperty($handler, 'combinationHelper'));
        $this->assertSame($this->currentSetting, $this->extractProperty($handler, 'currentSetting'));
        $this->assertSame($this->settingHelper, $this->extractProperty($handler, 'settingHelper'));
        $this->assertSame($this->sidebarEntitiesHelper, $this->extractProperty($handler, 'sidebarEntitiesHelper'));
        $this->assertSame($scriptVersion, $this->extractProperty($handler, 'scriptVersion'));
    }

    /**
     * Tests the handle method.
     * @throws Exception
     * @covers ::handle
     */
    public function testHandle(): void
    {
        $locale = 'abc';
        $scriptVersion = 'def';

        $setting = $this->createMock(SettingMetaData::class);

        $sidebarEntities = [
            $this->createMock(SidebarEntityData::class),
            $this->createMock(SidebarEntityData::class),
        ];

        $expectedTransfer = new InitData();
        $expectedTransfer->setSetting($setting)
                         ->setLocale($locale)
                         ->setSidebarEntities($sidebarEntities)
                         ->setScriptVersion($scriptVersion);

        $request = $this->createMock(ServerRequestInterface::class);

        $this->currentSetting->expects($this->once())
                             ->method('getLocale')
                             ->willReturn($locale);

        $this->settingHelper->expects($this->once())
                            ->method('createSettingMeta')
                            ->with($this->identicalTo($this->currentSetting))
                            ->willReturn($setting);

        $handler = $this->getMockBuilder(InitHandler::class)
                        ->onlyMethods(['updateCombinationStatus', 'updateSetting', 'getCurrentSidebarEntities'])
                        ->setConstructorArgs([
                            $this->apiClientFactory,
                            $this->combinationHelper,
                            $this->currentSetting,
                            $this->settingHelper,
                            $this->sidebarEntitiesHelper,
                            $scriptVersion,
                        ])
                        ->getMock();
        $handler->expects($this->once())
                ->method('updateCombinationStatus');
        $handler->expects($this->once())
                ->method('updateSetting');
        $handler->expects($this->once())
                ->method('getCurrentSidebarEntities')
                ->willReturn($sidebarEntities);

        $result = $handler->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        $this->assertEquals($expectedTransfer, $result->getTransfer());
    }

    /**
     * Tests the updateCombinationStatus method.
     * @throws ReflectionException
     * @covers ::updateCombinationStatus
     */
    public function testUpdateCombinationStatus(): void
    {
        $combination = $this->createMock(Combination::class);
        $response = $this->createMock(CombinationStatusResponse::class);

        $expectedRequest = new CombinationStatusRequest();

        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
                  ->method('fetchResponse')
                  ->with($this->equalTo($expectedRequest))
                  ->willReturn($response);

        $this->apiClientFactory->expects($this->once())
                               ->method('createWithoutFallback')
                               ->with($this->identicalTo($this->currentSetting))
                               ->willReturn($apiClient);

        $this->combinationHelper->expects($this->once())
                                ->method('hydrateStatusResponseToCombination')
                                ->with($this->identicalTo($response), $this->identicalTo($combination));

        $this->currentSetting->expects($this->once())
                             ->method('getCombination')
                             ->willReturn($combination);

        $handler = $this->getMockBuilder(InitHandler::class)
                        ->onlyMethods(['isCombinationStatusUpdateNeeded'])
                        ->setConstructorArgs([
                            $this->apiClientFactory,
                            $this->combinationHelper,
                            $this->currentSetting,
                            $this->settingHelper,
                            $this->sidebarEntitiesHelper,
                            '',
                        ])
                        ->getMock();
        $handler->expects($this->once())
                ->method('isCombinationStatusUpdateNeeded')
                ->willReturn(true);

        $this->invokeMethod($handler, 'updateCombinationStatus');
    }

    /**
     * Tests the updateCombinationStatus method.
     * @throws ReflectionException
     * @covers ::updateCombinationStatus
     */
    public function testUpdateCombinationStatusWithException(): void
    {
        $expectedRequest = new CombinationStatusRequest();

        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
                  ->method('fetchResponse')
                  ->with($this->equalTo($expectedRequest))
                  ->willThrowException($this->createMock(ApiClientException::class));

        $this->apiClientFactory->expects($this->once())
                               ->method('createWithoutFallback')
                               ->with($this->identicalTo($this->currentSetting))
                               ->willReturn($apiClient);

        $this->combinationHelper->expects($this->never())
                                ->method('hydrateStatusResponseToCombination');

        $this->expectException(FailedApiRequestException::class);

        $handler = $this->getMockBuilder(InitHandler::class)
                        ->onlyMethods(['isCombinationStatusUpdateNeeded'])
                        ->setConstructorArgs([
                            $this->apiClientFactory,
                            $this->combinationHelper,
                            $this->currentSetting,
                            $this->settingHelper,
                            $this->sidebarEntitiesHelper,
                            '',
                        ])
                        ->getMock();
        $handler->expects($this->once())
                ->method('isCombinationStatusUpdateNeeded')
                ->willReturn(true);

        $this->invokeMethod($handler, 'updateCombinationStatus');
    }

    /**
     * Tests the updateCombinationStatus method.
     * @throws ReflectionException
     * @covers ::updateCombinationStatus
     */
    public function testUpdateCombinationStatusWithoutNeed(): void
    {
        $this->apiClientFactory->expects($this->never())
                               ->method('createWithoutFallback');

        $this->combinationHelper->expects($this->never())
                                ->method('hydrateStatusResponseToCombination');

        $this->currentSetting->expects($this->never())
                             ->method('getCombination');

        $handler = $this->getMockBuilder(InitHandler::class)
                        ->onlyMethods(['isCombinationStatusUpdateNeeded'])
                        ->setConstructorArgs([
                            $this->apiClientFactory,
                            $this->combinationHelper,
                            $this->currentSetting,
                            $this->settingHelper,
                            $this->sidebarEntitiesHelper,
                            '',
                        ])
                        ->getMock();
        $handler->expects($this->once())
                ->method('isCombinationStatusUpdateNeeded')
                ->willReturn(false);

        $this->invokeMethod($handler, 'updateCombinationStatus');
    }

    /**
     * Provides the data for the isCombinationStatusUpdateNeeded test.
     * @return array<mixed>
     */
    public function provideIsCombinationStatusUpdateNeeded(): array
    {
        return [
            [CombinationStatus::ERRORED, null, true],
            [CombinationStatus::UNKNOWN, null, true],

            [CombinationStatus::AVAILABLE, null, true],
            [CombinationStatus::AVAILABLE, new DateTime('now'), false],
            [CombinationStatus::AVAILABLE, new DateTime('-2 days'), true],
        ];
    }

    /**
     * Tests the isCombinationStatusUpdateNeeded method.
     * @param string $status
     * @param DateTime|null $lastCheckTime
     * @param bool $expectedResult
     * @throws ReflectionException
     * @covers ::isCombinationStatusUpdateNeeded
     * @dataProvider provideIsCombinationStatusUpdateNeeded
     */
    public function testIsCombinationStatusUpdateNeeded(
        string $status,
        ?DateTime $lastCheckTime,
        bool $expectedResult
    ): void {
        $combination = new Combination();
        $combination->setStatus($status)
                    ->setLastCheckTime($lastCheckTime);

        $this->currentSetting->expects($this->once())
                             ->method('getCombination')
                             ->willReturn($combination);

        $handler = new InitHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentSetting,
            $this->settingHelper,
            $this->sidebarEntitiesHelper,
            ''
        );

        $result = $this->invokeMethod($handler, 'isCombinationStatusUpdateNeeded');

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the updateSetting method.
     * @throws ReflectionException
     * @covers ::updateSetting
     */
    public function testUpdateSetting(): void
    {
        $status = CombinationStatus::AVAILABLE;

        $combination = $this->createMock(Combination::class);
        $combination->expects($this->once())
                    ->method('getStatus')
                    ->willReturn($status);

        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
                  ->method('clearAuthorizationToken');

        $this->apiClientFactory->expects($this->once())
                               ->method('create')
                               ->with($this->identicalTo($this->currentSetting))
                               ->willReturn($apiClient);

        $this->currentSetting->expects($this->once())
                             ->method('getCombination')
                             ->willReturn($combination);
        $this->currentSetting->expects($this->once())
                             ->method('getHasData')
                             ->willReturn(false);
        $this->currentSetting->expects($this->once())
                             ->method('setHasData')
                             ->with($this->isTrue())
                             ->willReturnSelf();
        $this->currentSetting->expects($this->once())
                             ->method('setApiAuthorizationToken')
                             ->with($this->identicalTo(''))
                             ->willReturnSelf();

        $this->sidebarEntitiesHelper->expects($this->once())
                                    ->method('refreshLabels')
                                    ->with($this->identicalTo($this->currentSetting));

        $handler = new InitHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentSetting,
            $this->settingHelper,
            $this->sidebarEntitiesHelper,
            ''
        );

        $this->invokeMethod($handler, 'updateSetting');
    }

    /**
     * Tests the updateSetting method.
     * @throws ReflectionException
     * @covers ::updateSetting
     */
    public function testUpdateSettingWithoutChanges(): void
    {
        $status = CombinationStatus::AVAILABLE;

        $combination = $this->createMock(Combination::class);
        $combination->expects($this->once())
                    ->method('getStatus')
                    ->willReturn($status);

        $this->apiClientFactory->expects($this->never())
                               ->method('create');

        $this->currentSetting->expects($this->once())
                             ->method('getCombination')
                             ->willReturn($combination);
        $this->currentSetting->expects($this->once())
                             ->method('getHasData')
                             ->willReturn(true);
        $this->currentSetting->expects($this->never())
                             ->method('setHasData');

        $this->sidebarEntitiesHelper->expects($this->never())
                                    ->method('refreshLabels')
                                    ->with($this->identicalTo($this->currentSetting));

        $handler = new InitHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentSetting,
            $this->settingHelper,
            $this->sidebarEntitiesHelper,
            ''
        );

        $this->invokeMethod($handler, 'updateSetting');
    }

    /**
     * Tests the getCurrentSidebarEntities method.
     * @throws ReflectionException
     * @covers ::getCurrentSidebarEntities
     */
    public function testGetCurrentSidebarEntities(): void
    {
        $sidebarEntities = [
            $this->createMock(SidebarEntity::class),
            $this->createMock(SidebarEntity::class),
        ];

        $mappedSidebarEntities = [
            $this->createMock(SidebarEntityData::class),
            $this->createMock(SidebarEntityData::class),
        ];

        $this->currentSetting->expects($this->once())
                             ->method('getSidebarEntities')
                             ->willReturn(new ArrayCollection($sidebarEntities));

        $this->sidebarEntitiesHelper->expects($this->once())
                                    ->method('mapEntities')
                                    ->with($this->identicalTo($sidebarEntities))
                                    ->willReturn($mappedSidebarEntities);

        $handler = new InitHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentSetting,
            $this->settingHelper,
            $this->sidebarEntitiesHelper,
            ''
        );
        $result = $this->invokeMethod($handler, 'getCurrentSidebarEntities');

        $this->assertSame($mappedSidebarEntities, $result);
    }
}
