<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Setting;

use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\CombinationStatus;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Handler\Setting\SaveHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
use FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper;
use FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository;
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
 * @covers \FactorioItemBrowser\PortalApi\Server\Handler\Setting\SaveHandler
 */
class SaveHandlerTest extends TestCase
{
    /** @var CombinationHelper&MockObject */
    private CombinationHelper $combinationHelper;
    /** @var User&MockObject */
    private User $currentUser;
    /** @var SettingRepository&MockObject */
    private SettingRepository $settingRepository;
    /** @var SidebarEntitiesHelper&MockObject */
    private SidebarEntitiesHelper $sidebarEntitiesHelper;

    protected function setUp(): void
    {
        $this->combinationHelper = $this->createMock(CombinationHelper::class);
        $this->currentUser = $this->createMock(User::class);
        $this->settingRepository = $this->createMock(SettingRepository::class);
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
                        $this->combinationHelper,
                        $this->currentUser,
                        $this->settingRepository,
                        $this->sidebarEntitiesHelper,
                    ])
                    ->getMock();
    }

    /**
     * @throws Exception
     */
    public function testHandleWithNewSetting(): void
    {
        $combination = new Combination();
        $combination->setStatus(CombinationStatus::AVAILABLE);

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
                          ->willReturn(null);

        $this->combinationHelper->expects($this->once())
                                ->method('createForCombinationId')
                                ->with($this->equalTo(Uuid::fromString('78c85405-bfc0-4600-acd9-b992c871812b')))
                                ->willReturn($combination);
        $this->combinationHelper->expects($this->never())
                                ->method('triggerExport');

        $this->settingRepository->expects($this->once())
                                ->method('createSetting')
                                ->with($this->identicalTo($this->currentUser), $this->identicalTo($combination))
                                ->willReturn($setting);

        $this->sidebarEntitiesHelper->expects($this->once())
                                    ->method('refreshLabels')
                                    ->with($this->identicalTo($setting));

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(EmptyResponse::class, $result);
    }

    /**
     * @throws Exception
     */
    public function testHandleWithUnknownCombination(): void
    {
        $combination = new Combination();
        $combination->setStatus(CombinationStatus::UNKNOWN);

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
                          ->willReturn(null);

        $this->combinationHelper->expects($this->once())
                                ->method('createForCombinationId')
                                ->with($this->equalTo(Uuid::fromString('78c85405-bfc0-4600-acd9-b992c871812b')))
                                ->willReturn($combination);
        $this->combinationHelper->expects($this->once())
                                ->method('triggerExport')
                                ->with($this->identicalTo($combination));

        $this->settingRepository->expects($this->once())
                                ->method('createSetting')
                                ->with($this->identicalTo($this->currentUser), $this->identicalTo($combination))
                                ->willReturn($setting);

        $this->sidebarEntitiesHelper->expects($this->once())
                                    ->method('refreshLabels')
                                    ->with($this->identicalTo($setting));

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(EmptyResponse::class, $result);
    }

    /**
     * @throws Exception
     */
    public function testHandleWithExistingSetting(): void
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

        $this->combinationHelper->expects($this->never())
                                ->method('createForCombinationId');
        $this->combinationHelper->expects($this->never())
                                ->method('triggerExport');

        $this->settingRepository->expects($this->never())
                                ->method('createSetting');

        $this->sidebarEntitiesHelper->expects($this->once())
                                    ->method('refreshLabels')
                                    ->with($this->identicalTo($setting));

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(EmptyResponse::class, $result);
    }
}
