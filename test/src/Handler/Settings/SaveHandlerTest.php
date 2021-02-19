<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Settings;

use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\MissingSettingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\Settings\SaveHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingOptionsData;
use Laminas\Diactoros\Response\EmptyResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the SaveHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Handler\Settings\SaveHandler
 */
class SaveHandlerTest extends TestCase
{
    /** @var User&MockObject */
    private User $currentUser;
    /** @var SidebarEntitiesHelper&MockObject */
    private SidebarEntitiesHelper $sidebarEntitiesHelper;

    protected function setUp(): void
    {
        $this->currentUser = $this->createMock(User::class);
        $this->sidebarEntitiesHelper = $this->createMock(SidebarEntitiesHelper::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return SaveHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): SaveHandler
    {
        return $this->getMockBuilder(SaveHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->currentUser,
                        $this->sidebarEntitiesHelper,
                    ])
                    ->getMock();
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandle(): void
    {
        $requestOptions = new SettingOptionsData();
        $requestOptions->name = 'abc';
        $requestOptions->recipeMode = 'def';
        $requestOptions->locale = 'ghi';

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getAttribute')
                ->willReturnMap([
                    ['combination-id', '', '78c85405-bfc0-4600-acd9-b992c871812b'],
                ]);
        $request->expects($this->any())
                ->method('getParsedBody')
                ->willReturn($requestOptions);

        $setting = $this->createMock(Setting::class);
        $setting->expects($this->once())
                ->method('setName')
                ->with($this->identicalTo('abc'))
                ->willReturnSelf();
        $setting->expects($this->once())
                ->method('setRecipeMode')
                ->with($this->identicalTo('def'))
                ->willReturnSelf();
        $setting->expects($this->once())
                ->method('setLocale')
                ->with($this->identicalTo('ghi'))
                ->willReturnSelf();
        $setting->expects($this->once())
                ->method('setIsTemporary')
                ->with($this->isFalse())
                ->willReturnSelf();

        $this->currentUser->expects($this->once())
                          ->method('getSettingByCombinationId')
                          ->with($this->equalTo(Uuid::fromString('78c85405-bfc0-4600-acd9-b992c871812b')))
                          ->willReturn($setting);

        $this->sidebarEntitiesHelper->expects($this->once())
                                    ->method('refreshLabels')
                                    ->with($this->identicalTo($setting));

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(EmptyResponse::class, $result);
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithoutSetting(): void
    {
        $requestOptions = new SettingOptionsData();
        $requestOptions->name = 'abc';
        $requestOptions->recipeMode = 'def';
        $requestOptions->locale = 'ghi';

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getAttribute')
                ->willReturnMap([
                    ['combination-id', '', '78c85405-bfc0-4600-acd9-b992c871812b'],
                ]);
        $request->expects($this->any())
                ->method('getParsedBody')
                ->willReturn($requestOptions);

        $this->currentUser->expects($this->once())
                          ->method('getSettingByCombinationId')
                          ->with($this->equalTo(Uuid::fromString('78c85405-bfc0-4600-acd9-b992c871812b')))
                          ->willReturn(null);

        $this->sidebarEntitiesHelper->expects($this->never())
                                    ->method('refreshLabels');

        $this->expectException(MissingSettingException::class);

        $instance = $this->createInstance();
        $instance->handle($request);
    }
}
