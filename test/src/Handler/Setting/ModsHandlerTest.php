<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Setting;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Request\Mod\ModListRequest;
use FactorioItemBrowser\Api\Client\Response\Mod\ModListResponse;
use FactorioItemBrowser\Api\Client\Transfer\Mod;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MissingSettingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\Setting\ModsHandler;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\ModData;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the ModsHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Handler\Setting\ModsHandler
 */
class ModsHandlerTest extends TestCase
{
    /** @var ClientInterface&MockObject */
    private ClientInterface $apiClient;
    /** @var User&MockObject */
    private User $currentUser;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;

    protected function setUp(): void
    {
        $this->apiClient = $this->createMock(ClientInterface::class);
        $this->currentUser = $this->createMock(User::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return ModsHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): ModsHandler
    {
        return $this->getMockBuilder(ModsHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->apiClient,
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
        $combinationIdString = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';
        $combinationId = Uuid::fromString($combinationIdString);

        $combination = new Combination();
        $combination->setId($combinationId);

        $setting = new Setting();
        $setting->setCombination($combination)
                ->setLocale('abc');

        $mod1 = $this->createMock(Mod::class);
        $mod2 = $this->createMock(Mod::class);
        $modData1 = $this->createMock(ModData::class);
        $modData2 = $this->createMock(ModData::class);
        $expectedTransfer = [$modData1, $modData2];

        $expectedApiRequest = new ModListRequest();
        $expectedApiRequest->combinationId = $combinationIdString;
        $expectedApiRequest->locale = 'abc';

        $apiResponse = new ModListResponse();
        $apiResponse->mods = [$mod1, $mod2];

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo('combination-id'), $this->identicalTo(''))
                ->willReturn($combinationIdString);

        $this->currentUser->expects($this->once())
                          ->method('getSettingByCombinationId')
                          ->with($this->equalTo($combinationId))
                          ->willReturn($setting);

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willReturn(new FulfilledPromise($apiResponse));

        $this->mapperManager->expects($this->exactly(2))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($mod1), $this->isInstanceOf(ModData::class)],
                                [$this->identicalTo($mod2), $this->isInstanceOf(ModData::class)],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $modData1,
                                $modData2,
                            );

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
        $combinationIdString = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';
        $combinationId = Uuid::fromString($combinationIdString);

        $combination = new Combination();
        $combination->setId($combinationId);

        $setting = new Setting();
        $setting->setCombination($combination)
                ->setLocale('abc');

        $expectedApiRequest = new ModListRequest();
        $expectedApiRequest->combinationId = $combinationIdString;
        $expectedApiRequest->locale = 'abc';

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo('combination-id'), $this->identicalTo(''))
                ->willReturn($combinationIdString);

        $this->currentUser->expects($this->once())
                          ->method('getSettingByCombinationId')
                          ->with($this->equalTo($combinationId))
                          ->willReturn($setting);

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willReturn(new RejectedPromise($this->createMock(ClientException::class)));

        $this->mapperManager->expects($this->never())
                            ->method('map');

        $this->expectException(FailedApiRequestException::class);

        $instance = $this->createInstance();
        $instance->handle($request);
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithMissingSetting(): void
    {
        $combinationIdString = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';
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

        $this->apiClient->expects($this->never())
                        ->method('sendRequest');

        $this->mapperManager->expects($this->never())
                            ->method('map');

        $this->expectException(MissingSettingException::class);

        $instance = $this->createInstance();
        $instance->handle($request);
    }
}
