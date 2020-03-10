<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Settings;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use Doctrine\Common\Collections\Collection;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\Mod;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\Settings\ListHandler;
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
     * The mocked api client.
     * @var ApiClientInterface&MockObject
     */
    protected $apiClient;

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

        $this->apiClient = $this->createMock(ApiClientInterface::class);
        $this->currentSetting = $this->createMock(Setting::class);
        $this->currentUser = $this->createMock(User::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new ListHandler($this->apiClient, $this->currentSetting, $this->currentUser, $this->mapperManager);

        $this->assertSame($this->apiClient, $this->extractProperty($handler, 'apiClient'));
        $this->assertSame($this->currentSetting, $this->extractProperty($handler, 'currentSetting'));
        $this->assertSame($this->currentUser, $this->extractProperty($handler, 'currentUser'));
        $this->assertSame($this->mapperManager, $this->extractProperty($handler, 'mapperManager'));
    }

    /**
     * Tests the handle method.
     * @throws PortalApiServerException
     * @covers ::handle
     */
    public function testHandle(): void
    {
        $currentMods = [
            $this->createMock(Mod::class),
            $this->createMock(Mod::class),
        ];

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var SettingDetailsData&MockObject $settingDetails */
        $settingDetails = $this->createMock(SettingDetailsData::class);

        /* @var Setting&MockObject $setting1 */
        $setting1 = $this->createMock(Setting::class);
        /* @var Setting&MockObject $setting2 */
        $setting2 = $this->createMock(Setting::class);
        /* @var SettingMetaData&MockObject $settingMeta1 */
        $settingMeta1 = $this->createMock(SettingMetaData::class);
        /* @var SettingMetaData&MockObject $settingMeta2 */
        $settingMeta2 = $this->createMock(SettingMetaData::class);

        $expectedTransfer = new SettingsListData();
        $expectedTransfer->setSettings([$settingMeta1, $settingMeta2])
                         ->setCurrentSetting($settingDetails);

        /* @var Collection&MockObject $settingsCollection */
        $settingsCollection = $this->createMock(Collection::class);
        $settingsCollection->expects($this->once())
                           ->method('toArray')
                           ->willReturn([$setting1, $setting2]);

        $this->currentUser->expects($this->once())
                          ->method('getSettings')
                          ->willReturn($settingsCollection);

        /* @var ListHandler&MockObject $handler */
        $handler = $this->getMockBuilder(ListHandler::class)
                        ->onlyMethods(['fetchMods', 'mapSettingDetails', 'mapSettingMeta'])
                        ->setConstructorArgs([
                            $this->apiClient,
                            $this->currentSetting,
                            $this->currentUser,
                            $this->mapperManager,
                        ])
                        ->getMock();
        $handler->expects($this->once())
                ->method('fetchMods')
                ->with($this->identicalTo($this->apiClient))
                ->willReturn($currentMods);
        $handler->expects($this->once())
                ->method('mapSettingDetails')
                ->with($this->identicalTo($this->currentSetting), $this->identicalTo($currentMods))
                ->willReturn($settingDetails);
        $handler->expects($this->exactly(2))
                ->method('mapSettingMeta')
                ->withConsecutive(
                    [$this->identicalTo($setting1)],
                    [$this->identicalTo($setting2)]
                )
                ->willReturnOnConsecutiveCalls(
                    $settingMeta1,
                    $settingMeta2
                );

        /* @var TransferResponse $result */
        $result = $handler->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        $this->assertEquals($expectedTransfer, $result->getTransfer());
    }
}
