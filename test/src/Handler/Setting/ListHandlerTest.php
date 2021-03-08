<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Setting;

use BluePsyduck\MapperManager\MapperManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Handler\Setting\ListHandler;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the ListHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Handler\Setting\ListHandler
 */
class ListHandlerTest extends TestCase
{
    /** @var Setting&MockObject */
    private Setting $currentSetting;
    /** @var User&MockObject */
    private User $currentUser;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;

    protected function setUp(): void
    {
        $this->currentSetting = $this->createMock(Setting::class);
        $this->currentUser = $this->createMock(User::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
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
                        $this->mapperManager,
                    ])
                    ->getMock();
    }

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

        $settingData1 = $this->createMock(SettingData::class);
        $settingData2 = $this->createMock(SettingData::class);
        $settingData3 = $this->createMock(SettingData::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $expectedTransfer = [$settingData1, $settingData2, $settingData3];

        $this->currentSetting->expects($this->any())
                             ->method('getId')
                             ->willReturn(Uuid::fromString('0eddb134-4bbf-49d5-b642-40eda0b87754'));

        $this->currentUser->expects($this->once())
                          ->method('getSettings')
                          ->willReturn(new ArrayCollection([$setting1, $setting2, $setting3, $setting4]));

        $this->mapperManager->expects($this->exactly(3))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($setting1), $this->isInstanceOf(SettingData::class)],
                                [$this->identicalTo($setting3), $this->isInstanceOf(SettingData::class)],
                                [$this->identicalTo($setting4), $this->isInstanceOf(SettingData::class)],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $settingData1,
                                $settingData2,
                                $settingData3,
                            );

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        /* @var TransferResponse $result */
        $this->assertEquals($expectedTransfer, $result->getTransfer());
    }
}
