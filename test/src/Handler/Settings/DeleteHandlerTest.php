<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Settings;

use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\DeleteActiveSettingException;
use FactorioItemBrowser\PortalApi\Server\Exception\MissingSettingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\Settings\DeleteHandler;
use FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository;
use Laminas\Diactoros\Response\EmptyResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the DeleteHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Handler\Settings\DeleteHandler
 */
class DeleteHandlerTest extends TestCase
{
    /** @var Setting&MockObject */
    private Setting $currentSetting;
    /** @var User&MockObject */
    private User $currentUser;
    /** @var SettingRepository&MockObject */
    private SettingRepository $settingRepository;

    protected function setUp(): void
    {
        $this->currentSetting = $this->createMock(Setting::class);
        $this->currentUser = $this->createMock(User::class);
        $this->settingRepository = $this->createMock(SettingRepository::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return DeleteHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): DeleteHandler
    {
        return $this->getMockBuilder(DeleteHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->currentSetting,
                        $this->currentUser,
                        $this->settingRepository,
                    ])
                    ->getMock();
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandle(): void
    {
        $combinationIdString = '0b08779e-9639-4730-b9fc-66a1ceaeb216';
        $combinationId = Uuid::fromString($combinationIdString);

        $setting = new Setting();
        $setting->setId(Uuid::fromString('15c38bec-60d9-4908-ab4e-aa0f14036221'));

        $this->currentSetting->expects($this->any())
                             ->method('getId')
                             ->willReturn(Uuid::fromString('2b0afa54-bcf7-4152-a439-3d53cedbf720'));

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo('combination-id'), $this->identicalTo(''))
                ->willReturn($combinationIdString);

        $this->currentUser->expects($this->once())
                          ->method('getSettingByCombinationId')
                          ->with($this->equalTo($combinationId))
                          ->willReturn($setting);

        $this->settingRepository->expects($this->once())
                                ->method('deleteSetting')
                                ->with($this->identicalTo($setting));

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(EmptyResponse::class, $result);
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithMissingSettingException(): void
    {
        $combinationIdString = '0b08779e-9639-4730-b9fc-66a1ceaeb216';
        $combinationId = Uuid::fromString($combinationIdString);

        $this->currentSetting->expects($this->any())
                             ->method('getId')
                             ->willReturn(Uuid::fromString('2b0afa54-bcf7-4152-a439-3d53cedbf720'));

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo('combination-id'), $this->identicalTo(''))
                ->willReturn($combinationIdString);

        $this->currentUser->expects($this->once())
                          ->method('getSettingByCombinationId')
                          ->with($this->equalTo($combinationId))
                          ->willReturn(null);

        $this->settingRepository->expects($this->never())
                                ->method('deleteSetting');

        $this->expectException(MissingSettingException::class);

        $instance = $this->createInstance();
        $instance->handle($request);
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithDeleteActiveSettingException(): void
    {
        $combinationIdString = '0b08779e-9639-4730-b9fc-66a1ceaeb216';
        $combinationId = Uuid::fromString($combinationIdString);

        $setting = new Setting();
        $setting->setId(Uuid::fromString('15c38bec-60d9-4908-ab4e-aa0f14036221'));

        $this->currentSetting->expects($this->any())
                             ->method('getId')
                             ->willReturn(Uuid::fromString('15c38bec-60d9-4908-ab4e-aa0f14036221'));

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo('combination-id'), $this->identicalTo(''))
                ->willReturn($combinationIdString);

        $this->currentUser->expects($this->once())
                          ->method('getSettingByCombinationId')
                          ->with($this->equalTo($combinationId))
                          ->willReturn($setting);

        $this->settingRepository->expects($this->never())
                                ->method('deleteSetting');

        $this->expectException(DeleteActiveSettingException::class);

        $instance = $this->createInstance();
        $instance->handle($request);
    }
}
