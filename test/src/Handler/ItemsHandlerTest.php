<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Item\ItemListRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemListResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\ItemsHandler;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemListData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

/**
 * The PHPUnit test of the ItemsHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\ItemsHandler
 */
class ItemsHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked api client.
     * @var ApiClientInterface&MockObject
     */
    protected $apiClient;

    /**
     * The mocked mapper manager.
     * @var MapperManagerInterface&MockObject
     */
    protected $mapperManager;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClient = $this->createMock(ApiClientInterface::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new ItemsHandler($this->apiClient, $this->mapperManager);

        $this->assertSame($this->apiClient, $this->extractProperty($handler, 'apiClient'));
        $this->assertSame($this->mapperManager, $this->extractProperty($handler, 'mapperManager'));
    }

    /**
     * Tests the handle method.
     * @throws PortalApiServerException
     * @covers ::handle
     */
    public function testHandle(): void
    {
        $queryParams = [
            'numberOfResults' => '21',
            'indexOfFirstResult' => '42',
        ];

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getQueryParams')
                ->willReturn($queryParams);

        $itemListData = $this->createMock(ItemListData::class);
        $items = $this->createMock(ItemListResponse::class);

        $handler = $this->getMockBuilder(ItemsHandler::class)
                        ->onlyMethods(['fetchData', 'createItemListData'])
                        ->setConstructorArgs([$this->apiClient, $this->mapperManager])
                        ->getMock();
        $handler->expects($this->once())
                ->method('fetchData')
                ->with($this->identicalTo(21), $this->identicalTo(42))
                ->willReturn($items);
        $handler->expects($this->once())
                ->method('createItemListData')
                ->with($this->identicalTo($items))
                ->willReturn($itemListData);

        /* @var TransferResponse $result */
        $result = $handler->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        $this->assertSame($itemListData, $result->getTransfer());
    }

    /**
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchData(): void
    {
        $numberOfResults = 21;
        $indexOfFirstResult = 42;

        $expectedRequest = new ItemListRequest();
        $expectedRequest->setNumberOfResults(21)
                        ->setIndexOfFirstResult(42)
                        ->setNumberOfRecipesPerResult(0);

        $response = $this->createMock(ItemListResponse::class);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willReturn($response);

        $handler = new ItemsHandler($this->apiClient, $this->mapperManager);
        $result = $this->invokeMethod($handler, 'fetchData', $numberOfResults, $indexOfFirstResult);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchDataWithException(): void
    {
        $numberOfResults = 21;
        $indexOfFirstResult = 42;

        $expectedRequest = new ItemListRequest();
        $expectedRequest->setNumberOfResults(21)
                        ->setIndexOfFirstResult(42)
                        ->setNumberOfRecipesPerResult(0);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willThrowException($this->createMock(ApiClientException::class));

        $this->expectException(FailedApiRequestException::class);

        $handler = new ItemsHandler($this->apiClient, $this->mapperManager);
        $this->invokeMethod($handler, 'fetchData', $numberOfResults, $indexOfFirstResult);
    }

    /**
     * Tests the createItemListData method.
     * @throws ReflectionException
     * @covers ::createItemListData
     */
    public function testCreateItemListData(): void
    {
        $response = $this->createMock(ItemListResponse::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($response), $this->isInstanceOf(ItemListData::class));


        $handler = new ItemsHandler($this->apiClient, $this->mapperManager);
        $this->invokeMethod($handler, 'createItemListData', $response);
    }

    /**
     * Tests the createItemListData method.
     * @throws ReflectionException
     * @covers ::createItemListData
     */
    public function testCreateItemListDataWithException(): void
    {
        $response = $this->createMock(ItemListResponse::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($response), $this->isInstanceOf(ItemListData::class))
                            ->willThrowException($this->createMock(MapperException::class));

        $this->expectException(MappingException::class);

        $handler = new ItemsHandler($this->apiClient, $this->mapperManager);
        $this->invokeMethod($handler, 'createItemListData', $response);
    }
}
