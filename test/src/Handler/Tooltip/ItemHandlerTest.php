<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Tooltip;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Exception\NotFoundException;
use FactorioItemBrowser\Api\Client\Request\Item\ItemProductRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemProductResponse;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
use FactorioItemBrowser\PortalApi\Server\Handler\Tooltip\ItemHandler;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The PHPUnit test of the ItemHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Handler\Tooltip\ItemHandler
 */
class ItemHandlerTest extends TestCase
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
     * @return ItemHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): ItemHandler
    {
        return $this->getMockBuilder(ItemHandler::class)
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
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getAttribute')
                ->willReturnMap([
                    ['type', '', 'abc'],
                    ['name', '', 'def'],
                ]);

        $expectedApiRequest = new ItemProductRequest();
        $expectedApiRequest->type = 'abc';
        $expectedApiRequest->name = 'def';
        $expectedApiRequest->indexOfFirstResult = 0;
        $expectedApiRequest->numberOfResults = 7;

        $item = $this->createMock(GenericEntityWithRecipes::class);
        $apiResponse = new ItemProductResponse();
        $apiResponse->item = $item;

        $transfer = $this->createMock(EntityData::class);

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willReturn(new FulfilledPromise($apiResponse));

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($item), $this->isInstanceOf(EntityData::class))
                            ->willReturn($transfer);

        $this->numberOfRecipesPerResult = 7;

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        /* @var TransferResponse $result */
        $this->assertSame($transfer, $result->getTransfer());
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithoutItem(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getAttribute')
                ->willReturnMap([
                    ['type', '', 'abc'],
                    ['name', '', 'def'],
                ]);

        $expectedApiRequest = new ItemProductRequest();
        $expectedApiRequest->type = 'abc';
        $expectedApiRequest->name = 'def';
        $expectedApiRequest->indexOfFirstResult = 0;
        $expectedApiRequest->numberOfResults = 7;

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willThrowException($this->createMock(NotFoundException::class));

        $this->mapperManager->expects($this->never())
                            ->method('map');

        $this->numberOfRecipesPerResult = 7;

        $this->expectException(UnknownEntityException::class);

        $instance = $this->createInstance();
        $instance->handle($request);
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithApiException(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getAttribute')
                ->willReturnMap([
                    ['type', '', 'abc'],
                    ['name', '', 'def'],
                ]);

        $expectedApiRequest = new ItemProductRequest();
        $expectedApiRequest->type = 'abc';
        $expectedApiRequest->name = 'def';
        $expectedApiRequest->indexOfFirstResult = 0;
        $expectedApiRequest->numberOfResults = 7;

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
