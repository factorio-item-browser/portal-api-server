<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Item;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Exception\NotFoundException;
use FactorioItemBrowser\Api\Client\Request\Item\ItemProductRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemProductResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Handler\Item\ProductsHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ProductsHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Item\ProductsHandler
 */
class ProductsHandlerTest extends TestCase
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
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchData(): void
    {
        $type = 'abc';
        $name = 'def';
        $indexOfFirstResult = 42;
        $numberOfResults = 21;

        $expectedRequest = new ItemProductRequest();
        $expectedRequest->setType('abc')
                        ->setName('def')
                        ->setIndexOfFirstResult(42)
                        ->setNumberOfResults(21);

        /* @var GenericEntityWithRecipes&MockObject $item */
        $item = $this->createMock(GenericEntityWithRecipes::class);

        /* @var ItemProductResponse&MockObject $response */
        $response = $this->createMock(ItemProductResponse::class);
        $response->expects($this->once())
                 ->method('getItem')
                 ->willReturn($item);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willReturn($response);

        $handler = new ProductsHandler($this->apiClient, $this->mapperManager);
        $result = $this->invokeMethod($handler, 'fetchData', $type, $name, $indexOfFirstResult, $numberOfResults);

        $this->assertSame($item, $result);
    }

    /**
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchDataWithNotFoundException(): void
    {
        $type = 'abc';
        $name = 'def';
        $indexOfFirstResult = 42;
        $numberOfResults = 21;

        $expectedRequest = new ItemProductRequest();
        $expectedRequest->setType('abc')
                        ->setName('def')
                        ->setIndexOfFirstResult(42)
                        ->setNumberOfResults(21);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willThrowException($this->createMock(NotFoundException::class));

        $handler = new ProductsHandler($this->apiClient, $this->mapperManager);
        $result = $this->invokeMethod($handler, 'fetchData', $type, $name, $indexOfFirstResult, $numberOfResults);

        $this->assertNull($result);
    }

    /**
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchDataWithException(): void
    {
        $type = 'abc';
        $name = 'def';
        $indexOfFirstResult = 42;
        $numberOfResults = 21;

        $expectedRequest = new ItemProductRequest();
        $expectedRequest->setType('abc')
                        ->setName('def')
                        ->setIndexOfFirstResult(42)
                        ->setNumberOfResults(21);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willThrowException($this->createMock(ApiClientException::class));

        $this->expectException(FailedApiRequestException::class);

        $handler = new ProductsHandler($this->apiClient, $this->mapperManager);
        $this->invokeMethod($handler, 'fetchData', $type, $name, $indexOfFirstResult, $numberOfResults);
    }
}
