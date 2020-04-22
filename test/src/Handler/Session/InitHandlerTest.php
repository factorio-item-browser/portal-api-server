<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Session;

use BluePsyduck\TestHelper\ReflectionTrait;
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
use FactorioItemBrowser\PortalApi\Server\Handler\Session\InitHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
use FactorioItemBrowser\PortalApi\Server\Helper\SettingHelper;
use FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SessionInitData;
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
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Session\InitHandler
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
        $handler = new InitHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentSetting,
            $this->settingHelper,
            $this->sidebarEntitiesHelper
        );

        $this->assertSame($this->apiClientFactory, $this->extractProperty($handler, 'apiClientFactory'));
        $this->assertSame($this->combinationHelper, $this->extractProperty($handler, 'combinationHelper'));
        $this->assertSame($this->currentSetting, $this->extractProperty($handler, 'currentSetting'));
        $this->assertSame($this->settingHelper, $this->extractProperty($handler, 'settingHelper'));
        $this->assertSame($this->sidebarEntitiesHelper, $this->extractProperty($handler, 'sidebarEntitiesHelper'));
    }

    /**
     * Tests the handle method.
     * @throws Exception
     * @covers ::handle
     */
    public function testHandle(): void
    {
        $settingName = 'abc';
        $settingHash = 'def';
        $locale = 'ghi';

        $sidebarEntities = [
            $this->createMock(SidebarEntityData::class),
            $this->createMock(SidebarEntityData::class),
        ];

        $expectedTransfer = new SessionInitData();
        $expectedTransfer->setSettingName($settingName)
                         ->setSettingHash($settingHash)
                         ->setLocale($locale)
                         ->setSidebarEntities($sidebarEntities);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);

        $this->currentSetting->expects($this->once())
                             ->method('getName')
                             ->willReturn($settingName);
        $this->currentSetting->expects($this->once())
                             ->method('getLocale')
                             ->willReturn($locale);

        $this->settingHelper->expects($this->once())
                            ->method('calculateHash')
                            ->with($this->identicalTo($this->currentSetting))
                            ->willReturn($settingHash);

        /* @var InitHandler&MockObject $handler */
        $handler = $this->getMockBuilder(InitHandler::class)
                        ->onlyMethods(['updateCombinationStatus', 'updateSetting', 'getCurrentSidebarEntities'])
                        ->setConstructorArgs([
                            $this->apiClientFactory,
                            $this->combinationHelper,
                            $this->currentSetting,
                            $this->settingHelper,
                            $this->sidebarEntitiesHelper,
                        ])
                        ->getMock();
        $handler->expects($this->once())
                ->method('updateCombinationStatus');
        $handler->expects($this->once())
                ->method('updateSetting');
        $handler->expects($this->once())
                ->method('getCurrentSidebarEntities')
                ->willReturn($sidebarEntities);

        /* @var TransferResponse $result */
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
        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);
        /* @var CombinationStatusResponse&MockObject $response */
        $response = $this->createMock(CombinationStatusResponse::class);

        $expectedRequest = new CombinationStatusRequest();

        /* @var ApiClientInterface&MockObject $apiClient */
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

        $handler = new InitHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentSetting,
            $this->settingHelper,
            $this->sidebarEntitiesHelper
        );
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

        /* @var ApiClientInterface&MockObject $apiClient */
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

        $handler = new InitHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentSetting,
            $this->settingHelper,
            $this->sidebarEntitiesHelper
        );
        $this->invokeMethod($handler, 'updateCombinationStatus');
    }

    /**
     * Tests the updateSetting method.
     * @throws ReflectionException
     * @covers ::updateSetting
     */
    public function testUpdateSetting(): void
    {
        $status = CombinationStatus::AVAILABLE;

        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);
        $combination->expects($this->once())
                    ->method('getStatus')
                    ->willReturn($status);

        /* @var ApiClientInterface&MockObject $apiClient */
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
            $this->sidebarEntitiesHelper
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

        /* @var Combination&MockObject $combination */
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
            $this->sidebarEntitiesHelper
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
            $this->sidebarEntitiesHelper
        );
        $result = $this->invokeMethod($handler, 'getCurrentSidebarEntities');

        $this->assertSame($mappedSidebarEntities, $result);
    }
}
