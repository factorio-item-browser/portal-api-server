<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Request\Item\ItemRandomRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemRandomResponse;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\RandomHandler;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The PHPUnit test of the RandomHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Handler\RandomHandler
 */
class RandomHandlerTest extends TestCase
{
    /** @var ClientInterface&MockObject */
    private ClientInterface $apiClient;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;
    private int $numberOfRecipesPerResult = 42;

    protected function setUp(): void
    {
        $this->apiClient = $this->createMock(ClientInterface::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return RandomHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): RandomHandler
    {
        return $this->getMockBuilder(RandomHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->apiClient,
                        $this->mapperManager,
                        $this->numberOfRecipesPerResult,
                    ])
                    ->getMock();
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandle(): void
    {
        $queryParams = [
            'numberOfResults' => '21',
        ];

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getQueryParams')
                ->willReturn($queryParams);

        $expectedApiRequest = new ItemRandomRequest();
        $expectedApiRequest->numberOfResults = 21;
        $expectedApiRequest->numberOfRecipesPerResult = 7;

        $item1 = $this->createMock(GenericEntityWithRecipes::class);
        $item2 = $this->createMock(GenericEntityWithRecipes::class);
        $mappedItem1 = $this->createMock(EntityData::class);
        $mappedItem2 = $this->createMock(EntityData::class);

        $apiResponse = new ItemRandomResponse();
        $apiResponse->items = [$item1, $item2];

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willReturn(new FulfilledPromise($apiResponse));

        $this->mapperManager->expects($this->exactly(2))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($item1), $this->isInstanceOf(EntityData::class)],
                                [$this->identicalTo($item2), $this->isInstanceOf(EntityData::class)],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $mappedItem1,
                                $mappedItem2,
                            );

        $this->numberOfRecipesPerResult = 7;

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        /* @var TransferResponse $result */
        $this->assertSame([$mappedItem1, $mappedItem2], $result->getTransfer());
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithApiException(): void
    {
        $queryParams = [
            'numberOfResults' => '21',
        ];

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getQueryParams')
                ->willReturn($queryParams);

        $expectedApiRequest = new ItemRandomRequest();
        $expectedApiRequest->numberOfResults = 21;
        $expectedApiRequest->numberOfRecipesPerResult = 7;

        $item1 = $this->createMock(GenericEntityWithRecipes::class);
        $item2 = $this->createMock(GenericEntityWithRecipes::class);

        $apiResponse = new ItemRandomResponse();
        $apiResponse->items = [$item1, $item2];

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willThrowException($this->createMock(ClientException::class));

        $this->mapperManager->expects($this->never())
                            ->method('map');

        $this->numberOfRecipesPerResult = 7;

        $this->expectException(FailedApiRequestException::class);

        $instance = $this->createInstance();
        $instance->handle($request);
    }
}
