<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Settings;

use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\CombinationStatus;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Handler\Settings\CreateHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
use FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingCreateData;
use Laminas\Diactoros\Response\EmptyResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The PHPUnit test of the CreateHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Settings\CreateHandler
 */
class CreateHandlerTest extends TestCase
{
    /** @var CombinationHelper&MockObject */
    private CombinationHelper $combinationHelper;
    /** @var User&MockObject */
    private User $currentUser;
    /** @var SettingRepository&MockObject */
    private SettingRepository $settingRepository;

    protected function setUp(): void
    {
        $this->combinationHelper = $this->createMock(CombinationHelper::class);
        $this->currentUser = $this->createMock(User::class);
        $this->settingRepository = $this->createMock(SettingRepository::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return CreateHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): CreateHandler
    {
        return $this->getMockBuilder(CreateHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->combinationHelper,
                        $this->currentUser,
                        $this->settingRepository,
                    ])
                    ->getMock();
    }

    /**
     * @throws Exception
     */
    public function testHandle(): void
    {
        $settingData = new SettingCreateData();
        $settingData->modNames = ['abc', 'def'];
        $settingData->name = 'ghi';
        $settingData->recipeMode = 'jkl';
        $settingData->locale = 'mno';

        $combination = new Combination();
        $combination->setStatus(CombinationStatus::AVAILABLE);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn($settingData);

        $setting = $this->createMock(Setting::class);
        $setting->expects($this->once())
                ->method('setName')
                ->with($this->identicalTo('ghi'))
                ->willReturnSelf();
        $setting->expects($this->once())
                ->method('setRecipeMode')
                ->with($this->identicalTo('jkl'))
                ->willReturnSelf();
        $setting->expects($this->once())
                ->method('setLocale')
                ->with($this->identicalTo('mno'))
                ->willReturnSelf();

        $this->combinationHelper->expects($this->once())
                                ->method('createForModNames')
                                ->with($this->identicalTo(['abc', 'def']))
                                ->willReturn($combination);
        $this->combinationHelper->expects($this->never())
                                ->method('triggerExport');

        $this->settingRepository->expects($this->once())
                                ->method('createSetting')
                                ->with($this->identicalTo($this->currentUser), $this->identicalTo($combination))
                                ->willReturn($setting);

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(EmptyResponse::class, $result);
    }

    /**
     * @throws Exception
     */
    public function testHandleWithTrigger(): void
    {
        $settingData = new SettingCreateData();
        $settingData->modNames = ['abc', 'def'];
        $settingData->name = 'ghi';
        $settingData->recipeMode = 'jkl';
        $settingData->locale = 'mno';

        $combination = new Combination();
        $combination->setStatus(CombinationStatus::UNKNOWN);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn($settingData);

        $setting = $this->createMock(Setting::class);
        $setting->expects($this->once())
                ->method('setName')
                ->with($this->identicalTo('ghi'))
                ->willReturnSelf();
        $setting->expects($this->once())
                ->method('setRecipeMode')
                ->with($this->identicalTo('jkl'))
                ->willReturnSelf();
        $setting->expects($this->once())
                ->method('setLocale')
                ->with($this->identicalTo('mno'))
                ->willReturnSelf();

        $this->combinationHelper->expects($this->once())
                                ->method('createForModNames')
                                ->with($this->identicalTo(['abc', 'def']))
                                ->willReturn($combination);
        $this->combinationHelper->expects($this->once())
                                ->method('triggerExport')
                                ->with($this->identicalTo($combination));

        $this->settingRepository->expects($this->once())
                                ->method('createSetting')
                                ->with($this->identicalTo($this->currentUser), $this->identicalTo($combination))
                                ->willReturn($setting);

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(EmptyResponse::class, $result);
    }
}
