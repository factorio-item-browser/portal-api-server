<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Settings;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\DeleteActiveSettingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\Settings\DeleteHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\SettingHelper;
use FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository;
use Laminas\Diactoros\Response\EmptyResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use ReflectionException;

/**
 * The PHPUnit test of the DeleteHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Settings\DeleteHandler
 */
class DeleteHandlerTest extends TestCase
{
    use ReflectionTrait;

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
     * The mocked setting repository.
     * @var SettingRepository&MockObject
     */
    protected $settingRepository;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->currentUser = $this->createMock(User::class);
        $this->settingHelper = $this->createMock(SettingHelper::class);
        $this->settingRepository = $this->createMock(SettingRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new DeleteHandler($this->currentUser, $this->settingHelper, $this->settingRepository);

        $this->assertSame($this->currentUser, $this->extractProperty($handler, 'currentUser'));
        $this->assertSame($this->settingHelper, $this->extractProperty($handler, 'settingHelper'));
        $this->assertSame($this->settingRepository, $this->extractProperty($handler, 'settingRepository'));
    }

    /**
     * Tests the handle method.
     * @throws PortalApiServerException
     * @covers ::handle
     */
    public function testHandle(): void
    {
        $combinationIdString = 'ed770932-42bc-4093-9572-e2287113be90';
        $combinationId = Uuid::fromString($combinationIdString);

        $currentSettingIdString = '0db518a9-6829-4a56-81f6-8b82d3a9d676';
        $currentSettingId = Uuid::fromString($currentSettingIdString);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo('combination-id'), $this->identicalTo(''))
                ->willReturn($combinationIdString);

        $setting = $this->createMock(Setting::class);
        $setting->expects($this->once())
                ->method('getId')
                ->willReturn($combinationId);

        $currentSetting = $this->createMock(Setting::class);
        $currentSetting->expects($this->once())
                       ->method('getId')
                       ->willReturn($currentSettingId);

        $this->currentUser->expects($this->atLeastOnce())
                          ->method('getCurrentSetting')
                          ->willReturn($currentSetting);

        $this->settingHelper->expects($this->once())
                            ->method('findInCurrentUser')
                            ->with($this->equalTo($combinationId))
                            ->willReturn($setting);

        $this->settingRepository->expects($this->once())
                                ->method('deleteSetting')
                                ->with($this->identicalTo($setting));

        $handler = new DeleteHandler($this->currentUser, $this->settingHelper, $this->settingRepository);
        $result = $handler->handle($request);

        $this->assertInstanceOf(EmptyResponse::class, $result);
    }

    /**
     * Tests the handle method.
     * @throws PortalApiServerException
     * @covers ::handle
     */
    public function testHandleWithException(): void
    {
        $combinationIdString = 'ed770932-42bc-4093-9572-e2287113be90';
        $combinationId = Uuid::fromString($combinationIdString);

        $currentSettingIdString = 'ed770932-42bc-4093-9572-e2287113be90';
        $currentSettingId = Uuid::fromString($currentSettingIdString);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo('combination-id'), $this->identicalTo(''))
                ->willReturn($combinationIdString);

        $setting = $this->createMock(Setting::class);
        $setting->expects($this->once())
                ->method('getId')
                ->willReturn($combinationId);

        $currentSetting = $this->createMock(Setting::class);
        $currentSetting->expects($this->once())
                       ->method('getId')
                       ->willReturn($currentSettingId);

        $this->currentUser->expects($this->atLeastOnce())
                          ->method('getCurrentSetting')
                          ->willReturn($currentSetting);

        $this->settingHelper->expects($this->once())
                            ->method('findInCurrentUser')
                            ->with($this->equalTo($combinationId))
                            ->willReturn($setting);

        $this->settingRepository->expects($this->never())
                                ->method('deleteSetting');

        $this->expectException(DeleteActiveSettingException::class);

        $handler = new DeleteHandler($this->currentUser, $this->settingHelper, $this->settingRepository);
        $handler->handle($request);
    }
}
