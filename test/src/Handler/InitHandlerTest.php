<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler;

use BluePsyduck\MapperManager\MapperManagerInterface;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use FactorioItemBrowser\PortalApi\Server\Constant\CombinationStatus;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\InitHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
use FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\InitData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The PHPUnit test of the InitHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\InitHandler
 */
class InitHandlerTest extends TestCase
{
    /** @var CombinationHelper&MockObject */
    private CombinationHelper $combinationHelper;
    /** @var Setting&MockObject */
    private Setting $currentSetting;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;
    /** @var SidebarEntitiesHelper&MockObject */
    private SidebarEntitiesHelper $sidebarEntitiesHelper;
    private string $scriptVersion = 'foo';

    protected function setUp(): void
    {
        $this->combinationHelper = $this->createMock(CombinationHelper::class);
        $this->currentSetting = $this->createMock(Setting::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->sidebarEntitiesHelper = $this->createMock(SidebarEntitiesHelper::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return InitHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): InitHandler
    {
        return $this->getMockBuilder(InitHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->combinationHelper,
                        $this->currentSetting,
                        $this->mapperManager,
                        $this->sidebarEntitiesHelper,
                        $this->scriptVersion,
                    ])
                    ->getMock();
    }

    /**
     * @return array<mixed>
     */
    public function provideHandle(): array
    {
        $combination1 = new Combination();
        $combination1->setStatus(CombinationStatus::AVAILABLE)
                     ->setLastCheckTime(new DateTime());
        $combination2 = new Combination();
        $combination2->setStatus(CombinationStatus::AVAILABLE)
                     ->setLastCheckTime(new DateTime('-1 week'));
        $combination3 = new Combination();
        $combination3->setStatus(CombinationStatus::AVAILABLE);
        $combination4 = new Combination();
        $combination4->setStatus(CombinationStatus::PENDING)
                     ->setLastCheckTime(new DateTime());

        return [
            [$combination1, true, false, false],
            [$combination2, true, true, false],
            [$combination3, true, true, false],
            [$combination4, true, true, true],
            [$combination4, false, true, false],
        ];
    }

    /**
     * @param Combination $combination
     * @param bool $settingHasData
     * @param bool $expectCombinationStatusUpdate
     * @param bool $expectRefreshLabels
     * @throws PortalApiServerException
     * @dataProvider provideHandle
     */
    public function testHandle(
        Combination $combination,
        bool $settingHasData,
        bool $expectCombinationStatusUpdate,
        bool $expectRefreshLabels
    ): void {
        $request = $this->createMock(ServerRequestInterface::class);
        $settingData = $this->createMock(SettingData::class);
        $sidebarEntity1 = $this->createMock(SidebarEntity::class);
        $sidebarEntity2 = $this->createMock(SidebarEntity::class);
        $sidebarEntityData1 = $this->createMock(SidebarEntityData::class);
        $sidebarEntityData2 = $this->createMock(SidebarEntityData::class);

        $expectedTransfer = new InitData();
        $expectedTransfer->setting = $settingData;
        $expectedTransfer->sidebarEntities = [$sidebarEntityData1, $sidebarEntityData2];
        $expectedTransfer->scriptVersion = 'foo';

        $this->currentSetting->expects($this->any())
                             ->method('getCombination')
                             ->willReturn($combination);
        $this->currentSetting->expects($this->any())
                             ->method('getHasData')
                             ->willReturn($settingHasData);
        $this->currentSetting->expects($this->any())
                             ->method('getSidebarEntities')
                             ->willReturn(new ArrayCollection([$sidebarEntity1, $sidebarEntity2]));
        $this->currentSetting->expects($this->any())
                             ->method('getIsTemporary')
                             ->willReturn(false);

        $this->combinationHelper->expects($expectCombinationStatusUpdate ? $this->once() : $this->never())
                                ->method('updateStatus')
                                ->with($this->identicalTo($combination));

        $this->mapperManager->expects($this->exactly(3))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($this->currentSetting), $this->isInstanceOf(SettingData::class)],
                                [$this->identicalTo($sidebarEntity1), $this->isInstanceOf(SidebarEntityData::class)],
                                [$this->identicalTo($sidebarEntity2), $this->isInstanceOf(SidebarEntityData::class)],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $settingData,
                                $sidebarEntityData1,
                                $sidebarEntityData2
                            );

        $this->sidebarEntitiesHelper->expects($expectRefreshLabels ? $this->once() : $this->never())
                                    ->method('refreshLabels')
                                    ->with($this->identicalTo($this->currentSetting));

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        /* @var TransferResponse $result */
        $this->assertEquals($expectedTransfer, $result->getTransfer());
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithTemporarySetting(): void
    {
        $combination = new Combination();
        $combination->setStatus(CombinationStatus::AVAILABLE)
                    ->setLastCheckTime(new DateTime());

        $request = $this->createMock(ServerRequestInterface::class);
        $settingData = $this->createMock(SettingData::class);
        $sidebarEntity1 = $this->createMock(SidebarEntity::class);
        $sidebarEntity2 = $this->createMock(SidebarEntity::class);
        $sidebarEntityData1 = $this->createMock(SidebarEntityData::class);
        $sidebarEntityData2 = $this->createMock(SidebarEntityData::class);

        $expectedTransfer = new InitData();
        $expectedTransfer->setting = $settingData;
        $expectedTransfer->sidebarEntities = [$sidebarEntityData1, $sidebarEntityData2];
        $expectedTransfer->scriptVersion = 'foo';

        $this->currentSetting->expects($this->any())
                             ->method('getCombination')
                             ->willReturn($combination);
        $this->currentSetting->expects($this->any())
                             ->method('getHasData')
                             ->willReturn(true);
        $this->currentSetting->expects($this->any())
                             ->method('getSidebarEntities')
                             ->willReturn(new ArrayCollection([$sidebarEntity1, $sidebarEntity2]));
        $this->currentSetting->expects($this->any())
                             ->method('getIsTemporary')
                             ->willReturn(true);

        $this->mapperManager->expects($this->exactly(3))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($this->currentSetting), $this->isInstanceOf(SettingData::class)],
                                [$this->identicalTo($sidebarEntity1), $this->isInstanceOf(SidebarEntityData::class)],
                                [$this->identicalTo($sidebarEntity2), $this->isInstanceOf(SidebarEntityData::class)],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $settingData,
                                $sidebarEntityData1,
                                $sidebarEntityData2
                            );

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        /* @var TransferResponse $result */
        $this->assertEquals($expectedTransfer, $result->getTransfer());
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithLastUsedSetting(): void
    {
        $combination = new Combination();
        $combination->setStatus(CombinationStatus::AVAILABLE)
                    ->setLastCheckTime(new DateTime());

        $request = $this->createMock(ServerRequestInterface::class);
        $settingData = $this->createMock(SettingData::class);
        $sidebarEntity1 = $this->createMock(SidebarEntity::class);
        $sidebarEntity2 = $this->createMock(SidebarEntity::class);
        $sidebarEntityData1 = $this->createMock(SidebarEntityData::class);
        $sidebarEntityData2 = $this->createMock(SidebarEntityData::class);
        $lastUsedSetting = $this->createMock(Setting::class);
        $lastUsedSettingData = $this->createMock(SettingData::class);

        $expectedTransfer = new InitData();
        $expectedTransfer->setting = $settingData;
        $expectedTransfer->sidebarEntities = [$sidebarEntityData1, $sidebarEntityData2];
        $expectedTransfer->scriptVersion = 'foo';
        $expectedTransfer->lastUsedSetting = $lastUsedSettingData;

        $user = $this->createMock(User::class);
        $user->expects($this->once())
             ->method('getLastUsedSetting')
             ->willReturn($lastUsedSetting);

        $this->currentSetting->expects($this->any())
                             ->method('getCombination')
                             ->willReturn($combination);
        $this->currentSetting->expects($this->any())
                             ->method('getHasData')
                             ->willReturn(true);
        $this->currentSetting->expects($this->any())
                             ->method('getSidebarEntities')
                             ->willReturn(new ArrayCollection([$sidebarEntity1, $sidebarEntity2]));
        $this->currentSetting->expects($this->any())
                             ->method('getIsTemporary')
                             ->willReturn(true);
        $this->currentSetting->expects($this->any())
                             ->method('getUser')
                             ->willReturn($user);

        $this->mapperManager->expects($this->exactly(4))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($this->currentSetting), $this->isInstanceOf(SettingData::class)],
                                [$this->identicalTo($lastUsedSetting), $this->isInstanceOf(SettingData::class)],
                                [$this->identicalTo($sidebarEntity1), $this->isInstanceOf(SidebarEntityData::class)],
                                [$this->identicalTo($sidebarEntity2), $this->isInstanceOf(SidebarEntityData::class)],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $settingData,
                                $lastUsedSettingData,
                                $sidebarEntityData1,
                                $sidebarEntityData2
                            );

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        /* @var TransferResponse $result */
        $this->assertEquals($expectedTransfer, $result->getTransfer());
    }
}
