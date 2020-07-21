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

        $this->currentUser->expects($this->once())
                          ->method('getSettings')
                          ->willReturn(new ArrayCollection([$setting1, $setting2]));

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

        $handler = new ListHandler($this->currentSetting, $this->currentUser, $this->settingHelper);

        /* @var TransferResponse $result */
        $result = $handler->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        $this->assertEquals($expectedTransfer, $result->getTransfer());
    }
}
