<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Item;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Exception\NotFoundException;
use FactorioItemBrowser\Api\Client\Request\Item\ItemIngredientRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemIngredientResponse;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Handler\Item\IngredientsHandler;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the IngredientsHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Handler\Item\IngredientsHandler
 */
class IngredientsHandlerTest extends TestCase
{
    use ReflectionTrait;

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
     * @return IngredientsHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): IngredientsHandler
    {
        return $this->getMockBuilder(IngredientsHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->apiClient,
                        $this->mapperManager,
                    ])
                    ->getMock();
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchData(): void
    {
        $expectedApiRequest = new ItemIngredientRequest();
        $expectedApiRequest->type = 'abc';
        $expectedApiRequest->name = 'def';
        $expectedApiRequest->indexOfFirstResult = 42;
        $expectedApiRequest->numberOfResults = 21;

        $item = $this->createMock(GenericEntityWithRecipes::class);
        $apiResponse = new ItemIngredientResponse();
        $apiResponse->item = $item;

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willReturn(new FulfilledPromise($apiResponse));

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'fetchData', 'abc', 'def', 42, 21);

        $this->assertSame($item, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchDataWithoutItem(): void
    {
        $expectedApiRequest = new ItemIngredientRequest();
        $expectedApiRequest->type = 'abc';
        $expectedApiRequest->name = 'def';
        $expectedApiRequest->indexOfFirstResult = 42;
        $expectedApiRequest->numberOfResults = 21;

        $item = $this->createMock(GenericEntityWithRecipes::class);
        $apiResponse = new ItemIngredientResponse();
        $apiResponse->item = $item;

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willThrowException($this->createMock(NotFoundException::class));

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'fetchData', 'abc', 'def', 42, 21);

        $this->assertNull($result);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchDataWithApiException(): void
    {
        $expectedApiRequest = new ItemIngredientRequest();
        $expectedApiRequest->type = 'abc';
        $expectedApiRequest->name = 'def';
        $expectedApiRequest->indexOfFirstResult = 42;
        $expectedApiRequest->numberOfResults = 21;

        $item = $this->createMock(GenericEntityWithRecipes::class);
        $apiResponse = new ItemIngredientResponse();
        $apiResponse->item = $item;

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willThrowException($this->createMock(ClientException::class));

        $this->expectException(FailedApiRequestException::class);

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'fetchData', 'abc', 'def', 42, 21);
    }
}
