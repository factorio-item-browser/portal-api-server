<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Settings;

use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\MissingSettingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\Settings\DetailsHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\SettingHelper;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the DetailsHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Handler\Settings\DetailsHandler
 */
class DetailsHandlerTest extends TestCase
{
    /** @var User&MockObject */
    private User $currentUser;
    /** @var SettingHelper&MockObject */
    private SettingHelper $settingHelper;

    protected function setUp(): void
    {
        $this->currentUser = $this->createMock(User::class);
        $this->settingHelper = $this->createMock(SettingHelper::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return DetailsHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): DetailsHandler
    {
        return $this->getMockBuilder(DetailsHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
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
        $combinationIdString = 'a20ef5d4-59bf-48aa-9b72-2e5ddb2f2995';
        $combinationId = Uuid::fromString($combinationIdString);
        $setting = $this->createMock(Setting::class);
        $settingDetails = $this->createMock(SettingDetailsData::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo('combination-id'), $this->identicalTo(''))
                ->willReturn($combinationIdString);

        $this->currentUser->expects($this->once())
                          ->method('getSettingByCombinationId')
                          ->with($this->equalTo($combinationId))
                          ->willReturn($setting);

        $this->settingHelper->expects($this->once())
                            ->method('createSettingDetails')
                            ->with($this->identicalTo($setting))
                            ->willReturn($settingDetails);

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        /* @var TransferResponse $result */
        $this->assertSame($settingDetails, $result->getTransfer());
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithException(): void
    {
        $combinationIdString = 'a20ef5d4-59bf-48aa-9b72-2e5ddb2f2995';
        $combinationId = Uuid::fromString($combinationIdString);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo('combination-id'), $this->identicalTo(''))
                ->willReturn($combinationIdString);

        $this->currentUser->expects($this->once())
                          ->method('getSettingByCombinationId')
                          ->with($this->equalTo($combinationId))
                          ->willReturn(null);

        $this->settingHelper->expects($this->never())
                            ->method('createSettingDetails');

        $this->expectException(MissingSettingException::class);

        $instance = $this->createInstance();
        $instance->handle($request);
    }
}
