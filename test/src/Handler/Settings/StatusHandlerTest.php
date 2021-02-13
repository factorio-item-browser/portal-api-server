<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Settings;

use DateTime;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\Settings\StatusHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
use FactorioItemBrowser\PortalApi\Server\Helper\SettingHelper;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingStatusData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the StatusHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Handler\Settings\StatusHandler
 */
class StatusHandlerTest extends TestCase
{
    /** @var CombinationHelper&MockObject */
    private CombinationHelper $combinationHelper;
    /** @var Setting&MockObject */
    private Setting $currentSetting;
    /** @var User&MockObject */
    private User $currentUser;
    /** @var SettingHelper&MockObject */
    private SettingHelper $settingHelper;

    protected function setUp(): void
    {
        $this->combinationHelper = $this->createMock(CombinationHelper::class);
        $this->currentSetting = $this->createMock(Setting::class);
        $this->currentUser = $this->createMock(User::class);
        $this->settingHelper = $this->createMock(SettingHelper::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return StatusHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): StatusHandler
    {
        return $this->getMockBuilder(StatusHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->combinationHelper,
                        $this->currentSetting,
                        $this->currentUser,
                        $this->settingHelper,
                    ])
                    ->getMock();
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandle(): void
    {
        $exportTime = new DateTime('2038-01-19 03:14:07');
        $combinationId = Uuid::fromString('78c85405-bfc0-4600-acd9-b992c871812b');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn(null);

        $combination = new Combination();
        $combination->setId($combinationId)
                    ->setStatus('ghi')
                    ->setExportTime($exportTime);

        $this->currentSetting->expects($this->once())
                             ->method('getCombination')
                             ->willReturn($combination);

        $this->combinationHelper->expects($this->never())
                                ->method('createForModNames');
        $this->combinationHelper->expects($this->once())
                                ->method('updateStatus')
                                ->with($this->identicalTo($combination));

        $this->currentUser->expects($this->once())
                          ->method('getSettingByCombinationId')
                          ->with($this->identicalTo($combinationId))
                          ->willReturn(null);

        $expectedTransfer = new SettingStatusData();
        $expectedTransfer->status = 'ghi';
        $expectedTransfer->exportTime = $exportTime;

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        /* @var TransferResponse $result */
        $this->assertEquals($expectedTransfer, $result->getTransfer());
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithModNames(): void
    {
        $exportTime = new DateTime('2038-01-19 03:14:07');
        $combinationId = Uuid::fromString('78c85405-bfc0-4600-acd9-b992c871812b');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn(['abc', 'def']);

        $combination = new Combination();
        $combination->setId($combinationId)
                    ->setStatus('ghi')
                    ->setExportTime($exportTime);

        $this->combinationHelper->expects($this->once())
                                ->method('createForModNames')
                                ->with($this->identicalTo(['abc', 'def']))
                                ->willReturn($combination);
        $this->combinationHelper->expects($this->never())
                                ->method('updateStatus');

        $this->currentUser->expects($this->once())
                          ->method('getSettingByCombinationId')
                          ->with($this->identicalTo($combinationId))
                          ->willReturn(null);

        $expectedTransfer = new SettingStatusData();
        $expectedTransfer->status = 'ghi';
        $expectedTransfer->exportTime = $exportTime;

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        /* @var TransferResponse $result */
        $this->assertEquals($expectedTransfer, $result->getTransfer());
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithExistingSetting(): void
    {
        $exportTime = new DateTime('2038-01-19 03:14:07');
        $combinationId = Uuid::fromString('78c85405-bfc0-4600-acd9-b992c871812b');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn(null);

        $combination = new Combination();
        $combination->setId($combinationId)
                    ->setStatus('ghi')
                    ->setExportTime($exportTime);

        $existingSetting = $this->createMock(Setting::class);
        $existingSettingData = $this->createMock(SettingDetailsData::class);

        $this->currentSetting->expects($this->once())
                             ->method('getCombination')
                             ->willReturn($combination);

        $this->combinationHelper->expects($this->never())
                                ->method('createForModNames');
        $this->combinationHelper->expects($this->once())
                                ->method('updateStatus')
                                ->with($this->identicalTo($combination));

        $this->currentUser->expects($this->once())
                          ->method('getSettingByCombinationId')
                          ->with($this->identicalTo($combinationId))
                          ->willReturn($existingSetting);

        $this->settingHelper->expects($this->once())
                            ->method('createSettingDetailsWithoutMods')
                            ->with($this->identicalTo($existingSetting))
                            ->willReturn($existingSettingData);

        $expectedTransfer = new SettingStatusData();
        $expectedTransfer->status = 'ghi';
        $expectedTransfer->exportTime = $exportTime;
        $expectedTransfer->existingSetting = $existingSettingData;

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        /* @var TransferResponse $result */
        $this->assertEquals($expectedTransfer, $result->getTransfer());
    }
}
