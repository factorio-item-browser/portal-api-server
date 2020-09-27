<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Settings;

use BluePsyduck\TestHelper\ReflectionTrait;
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
use ReflectionException;

/**
 * The PHPUnit test of the ListHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Settings\ListHandler
 */
class ListHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked current setting.
     * @var Setting&MockObject
     */
    protected $currentSetting;

    /**
     * The mocked current user.
     * @var User&MockObject
     */
    protected $currentUser;

    /**
     * The mocked setting helper.
     * @var SettingHelper&MockObject
     */
    protected $settingHelper;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->currentSetting = $this->createMock(Setting::class);
        $this->currentUser = $this->createMock(User::class);
        $this->settingHelper = $this->createMock(SettingHelper::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new ListHandler($this->currentSetting, $this->currentUser, $this->settingHelper);

        $this->assertSame($this->currentUser, $this->extractProperty($handler, 'currentUser'));
        $this->assertSame($this->settingHelper, $this->extractProperty($handler, 'settingHelper'));
    }

    /**
     * Tests the handle method.
     * @throws PortalApiServerException
     * @covers ::handle
     */
    public function testHandle(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $settingDetails = $this->createMock(SettingDetailsData::class);
        $setting1 = $this->createMock(Setting::class);
        $setting2 = $this->createMock(Setting::class);
        $settingMeta1 = $this->createMock(SettingMetaData::class);
        $settingMeta2 = $this->createMock(SettingMetaData::class);

        $expectedTransfer = new SettingsListData();
        $expectedTransfer->setSettings([$settingMeta1, $settingMeta2])
                         ->setCurrentSetting($settingDetails);

        $this->settingHelper->expects($this->exactly(2))
                            ->method('createSettingMeta')
                            ->withConsecutive(
                                [$this->identicalTo($setting1)],
                                [$this->identicalTo($setting2)]
                            )
                            ->willReturnOnConsecutiveCalls(
                                $settingMeta1,
                                $settingMeta2
                            );
        $this->settingHelper->expects($this->once())
                            ->method('createSettingDetails')
                            ->with($this->identicalTo($this->currentSetting))
                            ->willReturn($settingDetails);

        $handler = $this->getMockBuilder(ListHandler::class)
                        ->onlyMethods(['getFilteredSettings'])
                        ->setConstructorArgs([$this->currentSetting, $this->currentUser, $this->settingHelper])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getFilteredSettings')
                ->willReturn([$setting1, $setting2]);

        /* @var TransferResponse $result */
        $result = $handler->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        $this->assertEquals($expectedTransfer, $result->getTransfer());
    }

    /**
     * Tests the getFilteredSettings method.
     * @throws ReflectionException
     * @covers ::getFilteredSettings
     */
    public function testGetFilteredSettings(): void
    {
        $currentSettingId = '0eddb134-4bbf-49d5-b642-40eda0b87754';

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

        $settings = [$setting1, $setting2, $setting3, $setting4];
        $expectedResult = [$setting1, $setting3, $setting4];

        $this->currentSetting->expects($this->any())
                             ->method('getId')
                             ->willReturn(Uuid::fromString($currentSettingId));

        $this->currentUser->expects($this->once())
                          ->method('getSettings')
                          ->willReturn(new ArrayCollection($settings));

        $handler = new ListHandler($this->currentSetting, $this->currentUser, $this->settingHelper);
        $result = $this->invokeMethod($handler, 'getFilteredSettings');

        $this->assertSame($expectedResult, $result);
    }
}
