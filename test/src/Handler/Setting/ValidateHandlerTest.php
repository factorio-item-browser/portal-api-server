<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Setting;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Request\Mod\ModListRequest;
use FactorioItemBrowser\Api\Client\Response\Mod\ModListResponse;
use FactorioItemBrowser\Api\Client\Transfer\Mod;
use FactorioItemBrowser\CombinationApi\Client\Response\Combination\ValidateResponse;
use FactorioItemBrowser\CombinationApi\Client\Transfer\ValidatedMod;
use FactorioItemBrowser\CombinationApi\Client\Transfer\ValidationProblem;
use FactorioItemBrowser\Common\Constant\Defaults;
use FactorioItemBrowser\PortalApi\Server\Constant\CombinationStatus;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\Setting\ValidateHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingValidationData;
use FactorioItemBrowser\PortalApi\Server\Transfer\ValidationProblemData;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the ValidateHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Handler\Setting\ValidateHandler
 */
class ValidateHandlerTest extends TestCase
{
    /** @var ClientInterface&MockObject */
    private ClientInterface $apiClient;
    /** @var CombinationHelper&MockObject */
    private CombinationHelper $combinationHelper;
    /** @var User&MockObject */
    private User $currentUser;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;

    protected function setUp(): void
    {
        $this->apiClient = $this->createMock(ClientInterface::class);
        $this->combinationHelper = $this->createMock(CombinationHelper::class);
        $this->currentUser = $this->createMock(User::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return ValidateHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): ValidateHandler
    {
        return $this->getMockBuilder(ValidateHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->apiClient,
                        $this->combinationHelper,
                        $this->currentUser,
                        $this->mapperManager,
                    ])
                    ->getMock();
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandle(): void
    {
        $modNames = ['foo', 'bar'];
        $factorioVersion = '1.2.3';

        $combination = new Combination();
        $combination->setId(Uuid::fromString('44721899-aee1-4c9c-9959-06b2483c533d'))
                    ->setStatus(CombinationStatus::UNKNOWN);

        $validationProblem1 = new ValidationProblem();
        $validationProblem1->type = 'abc';
        $validationProblem1->dependency = 'def';
        $validationProblem2 = new ValidationProblem();
        $validationProblem2->type = 'ghi';
        $validationProblem2->dependency = 'jkl';
        $validationProblem3 = new ValidationProblem();
        $validationProblem3->type = 'mno';

        $validatedMod1 = new ValidatedMod();
        $validatedMod1->name = 'pqr';
        $validatedMod1->version = 'stu';
        $validatedMod1->problems = [$validationProblem1];
        $validatedMod2 = new ValidatedMod();
        $validatedMod2->name = 'vwx';
        $validatedMod2->version = 'yza';
        $validatedMod3 = new ValidatedMod();
        $validatedMod3->name = 'bcd';
        $validatedMod3->version = 'efg';
        $validatedMod3->problems = [$validationProblem2, $validationProblem3];

        $validateResponse = new ValidateResponse();
        $validateResponse->isValid = true;
        $validateResponse->mods = [$validatedMod1, $validatedMod2, $validatedMod3];

        $validationProblemData1 = new ValidationProblemData();
        $validationProblemData1->mod = 'pqr';
        $validationProblemData1->version = 'stu';
        $validationProblemData1->type = 'abc';
        $validationProblemData1->dependency = 'def';
        $validationProblemData2 = new ValidationProblemData();
        $validationProblemData2->mod = 'bcd';
        $validationProblemData2->version = 'efg';
        $validationProblemData2->type = 'ghi';
        $validationProblemData2->dependency = 'jkl';
        $validationProblemData3 = new ValidationProblemData();
        $validationProblemData3->mod = 'bcd';
        $validationProblemData3->version = 'efg';
        $validationProblemData3->type = 'mno';
        $validationProblemData3->dependency = '';

        $expectedTransfer = new SettingValidationData();
        $expectedTransfer->combinationId = '44721899-aee1-4c9c-9959-06b2483c533d';
        $expectedTransfer->status = CombinationStatus::UNKNOWN;
        $expectedTransfer->validationProblems = [
            $validationProblemData1,
            $validationProblemData2,
            $validationProblemData3,
        ];

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn($modNames);

        $expectedVersionRequest = new ModListRequest();
        $expectedVersionRequest->combinationId = Defaults::COMBINATION_ID;
        $baseMod = new Mod();
        $baseMod->version = $factorioVersion;
        $versionResponse = new ModListResponse();
        $versionResponse->mods = [$baseMod];

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedVersionRequest))
                        ->willReturn(new FulfilledPromise($versionResponse));

        $this->combinationHelper->expects($this->once())
                                ->method('createForModNames')
                                ->with($this->identicalTo($modNames))
                                ->willReturn($combination);
        $this->combinationHelper->expects($this->once())
                                ->method('validate')
                                ->with($this->identicalTo($combination), $this->identicalTo($factorioVersion))
                                ->willReturn($validateResponse);

        $this->currentUser->expects($this->once())
                          ->method('getSettingByCombinationId')
                          ->with($this->equalTo(Uuid::fromString('44721899-aee1-4c9c-9959-06b2483c533d')))
                          ->willReturn(null);

        $this->mapperManager->expects($this->never())
                            ->method('map');

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        /* @var TransferResponse $result */
        $this->assertEquals($expectedTransfer, $result->getTransfer());
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithExistingCombination(): void
    {
        $modNames = ['foo', 'bar'];

        $combination = new Combination();
        $combination->setId(Uuid::fromString('44721899-aee1-4c9c-9959-06b2483c533d'))
                    ->setStatus(CombinationStatus::AVAILABLE);

        $expectedTransfer = new SettingValidationData();
        $expectedTransfer->combinationId = '44721899-aee1-4c9c-9959-06b2483c533d';
        $expectedTransfer->status = CombinationStatus::AVAILABLE;
        $expectedTransfer->isValid = true;
        $expectedTransfer->validationProblems = [];

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn($modNames);

        $this->apiClient->expects($this->never())
                        ->method('sendRequest');

        $this->combinationHelper->expects($this->once())
                                ->method('createForModNames')
                                ->with($this->identicalTo($modNames))
                                ->willReturn($combination);
        $this->combinationHelper->expects($this->never())
                                ->method('validate');

        $this->currentUser->expects($this->once())
                          ->method('getSettingByCombinationId')
                          ->with($this->equalTo(Uuid::fromString('44721899-aee1-4c9c-9959-06b2483c533d')))
                          ->willReturn(null);

        $this->mapperManager->expects($this->never())
                            ->method('map');

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        /* @var TransferResponse $result */
        $this->assertEquals($expectedTransfer, $result->getTransfer());
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithExistingSetting(): void
    {
        $modNames = ['foo', 'bar'];

        $combination = new Combination();
        $combination->setId(Uuid::fromString('44721899-aee1-4c9c-9959-06b2483c533d'))
                    ->setStatus(CombinationStatus::AVAILABLE);

        $setting = $this->createMock(Setting::class);
        $settingData = $this->createMock(SettingData::class);

        $expectedTransfer = new SettingValidationData();
        $expectedTransfer->combinationId = '44721899-aee1-4c9c-9959-06b2483c533d';
        $expectedTransfer->status = CombinationStatus::AVAILABLE;
        $expectedTransfer->isValid = true;
        $expectedTransfer->validationProblems = [];
        $expectedTransfer->existingSetting = $settingData;

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn($modNames);

        $this->apiClient->expects($this->never())
                        ->method('sendRequest');

        $this->combinationHelper->expects($this->once())
                                ->method('createForModNames')
                                ->with($this->identicalTo($modNames))
                                ->willReturn($combination);
        $this->combinationHelper->expects($this->never())
                                ->method('validate');

        $this->currentUser->expects($this->once())
                          ->method('getSettingByCombinationId')
                          ->with($this->equalTo(Uuid::fromString('44721899-aee1-4c9c-9959-06b2483c533d')))
                          ->willReturn($setting);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($setting), $this->isInstanceOf(SettingData::class))
                            ->willReturn($settingData);

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        /* @var TransferResponse $result */
        $this->assertEquals($expectedTransfer, $result->getTransfer());
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithApiException(): void
    {
        $modNames = ['foo', 'bar'];

        $combination = new Combination();
        $combination->setId(Uuid::fromString('44721899-aee1-4c9c-9959-06b2483c533d'))
                    ->setStatus(CombinationStatus::UNKNOWN);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn($modNames);

        $expectedVersionRequest = new ModListRequest();
        $expectedVersionRequest->combinationId = Defaults::COMBINATION_ID;

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedVersionRequest))
                        ->willReturn(new RejectedPromise($this->createMock(ClientException::class)));

        $this->combinationHelper->expects($this->once())
                                ->method('createForModNames')
                                ->with($this->identicalTo($modNames))
                                ->willReturn($combination);
        $this->combinationHelper->expects($this->never())
                                ->method('validate');

        $this->currentUser->expects($this->never())
                          ->method('getSettingByCombinationId');

        $this->mapperManager->expects($this->never())
                            ->method('map');

        $this->expectException(FailedApiRequestException::class);

        $instance = $this->createInstance();
        $instance->handle($request);
    }
}
