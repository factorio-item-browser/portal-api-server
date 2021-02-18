<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Request\Item\ItemListRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemListResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\ItemsHandler;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemListData;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The PHPUnit test of the ItemsHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Handler\ItemsHandler
 */
class ItemsHandlerTest extends TestCase
{
    /** @var ClientInterface&MockObject */
    private ClientInterface $apiClient;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;

    protected function setUp(): void
    {
        $this->apiClient = $this->createMock(ClientInterface::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return ItemsHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): ItemsHandler
    {
        return $this->getMockBuilder(ItemsHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->apiClient,
                        $this->mapperManager,
                    ])
                    ->getMock();
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandle(): void
    {
        $queryParams = [
            'indexOfFirstResult' => '42',
            'numberOfResults' => '21',
        ];

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getQueryParams')
                ->willReturn($queryParams);

        $expectedApiRequest = new ItemListRequest();
        $expectedApiRequest->indexOfFirstResult = 42;
        $expectedApiRequest->numberOfResults = 21;
        $expectedApiRequest->numberOfRecipesPerResult = 0;

        $apiResponse = $this->createMock(ItemListResponse::class);
        $transfer = $this->createMock(ItemListData::class);

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willReturn(new FulfilledPromise($apiResponse));

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($apiResponse), $this->isInstanceOf(ItemListData::class))
                            ->willReturn($transfer);

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        /* @var TransferResponse $result */
        $this->assertSame($transfer, $result->getTransfer());
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithApiException(): void
    {
        $queryParams = [
            'indexOfFirstResult' => '42',
            'numberOfResults' => '21',
        ];

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getQueryParams')
                ->willReturn($queryParams);

        $expectedApiRequest = new ItemListRequest();
        $expectedApiRequest->indexOfFirstResult = 42;
        $expectedApiRequest->numberOfResults = 21;
        $expectedApiRequest->numberOfRecipesPerResult = 0;

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willThrowException($this->createMock(ClientException::class));

        $this->mapperManager->expects($this->never())
                            ->method('map');

        $this->expectException(FailedApiRequestException::class);

        $instance = $this->createInstance();
        $instance->handle($request);
    }
}
