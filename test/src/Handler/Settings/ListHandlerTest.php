<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Settings;

use Doctrine\Common\Collections\ArrayCollection;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\Settings\ListHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\SettingHelper;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingsListData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the ListHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Handler\Settings\ListHandler
 */
class ListHandlerTest extends TestCase
{
    /** @var Setting&MockObject */
    private Setting $currentSetting;
    /** @var User&MockObject */
    private User $currentUser;
    /** @var SettingHelper&MockObject */
    private SettingHelper $settingHelper;

    protected function setUp(): void
    {
        $this->currentSetting = $this->createMock(Setting::class);
        $this->currentUser = $this->createMock(User::class);
        $this->settingHelper = $this->createMock(SettingHelper::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return ListHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): ListHandler
    {
        return $this->getMockBuilder(ListHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
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
        $setting1 = new Setting();
        $setting1->setId(Uuid::fromString('1b492bd9-512b-4403-af33-6d5c64b44954'))
                 ->setIsTemporary(false);

        $setting2 = new Setting();
        $setting2->setId(Uuid::fromString('2bb17cd0-093e-4bd2-b1d0-3040a2e1965b'))
                 ->setIsTemporary(true);

        $setting3 = new Setting();
        $setting3->setId(Uuid::fromString('0eddb134-4bbf-49d5-b642-40eda0b87754'))
                 ->setIsTemporary(true);

        $setting4 = new Setting();
        $setting4->setId(Uuid::fromString('4e39c25b-d3e3-4960-a599-648aa0564d73'))
                 ->setIsTemporary(false);

        $settingMeta1 = $this->createMock(SettingMetaData::class);
        $settingMeta2 = $this->createMock(SettingMetaData::class);
        $settingMeta3 = $this->createMock(SettingMetaData::class);
        $settingDetails = $this->createMock(SettingDetailsData::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $transfer = new SettingsListData();
        $transfer->settings = [$settingMeta1, $settingMeta2, $settingMeta3];
        $transfer->currentSetting = $settingDetails;

        $this->currentSetting->expects($this->any())
                             ->method('getId')
                             ->willReturn(Uuid::fromString('0eddb134-4bbf-49d5-b642-40eda0b87754'));

        $this->currentUser->expects($this->once())
                          ->method('getSettings')
                          ->willReturn(new ArrayCollection([$setting1, $setting2, $setting3, $setting4]));

        $this->settingHelper->expects($this->exactly(3))
                            ->method('createSettingMeta')
                            ->withConsecutive(
                                [$this->identicalTo($setting1)],
                                [$this->identicalTo($setting3)],
                                [$this->identicalTo($setting4)],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $settingMeta1,
                                $settingMeta2,
                                $settingMeta3
                            );
        $this->settingHelper->expects($this->once())
                            ->method('createSettingDetails')
                            ->with($this->identicalTo($this->currentSetting))
                            ->willReturn($settingDetails);

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        /* @var TransferResponse $result */
        $this->assertEquals($transfer, $result->getTransfer());
    }
}
