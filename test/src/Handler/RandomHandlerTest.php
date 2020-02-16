<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Item\ItemRandomRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemRandomResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\RandomHandler;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

/**
 * The PHPUnit test of the RandomHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\RandomHandler
 */
class RandomHandlerTest extends TestCase
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
        $numberOfRecipesPerResult = 42;

        $handler = new RandomHandler($this->apiClient, $this->mapperManager, $numberOfRecipesPerResult);

        $this->assertSame($this->apiClient, $this->extractProperty($handler, 'apiClient'));
        $this->assertSame($this->mapperManager, $this->extractProperty($handler, 'mapperManager'));
        $this->assertSame($numberOfRecipesPerResult, $this->extractProperty($handler, 'numberOfRecipesPerResult'));
    }

    /**
     * Tests the handle method.
     * @throws PortalApiServerException
     * @covers ::handle
     */
    public function testHandle(): void
    {
        $numberOfResults = 42;

        $queryParams = [
            'numberOfResults' => '42',
        ];

        /* @var GenericEntityWithRecipes&MockObject $item1 */
        $item1 = $this->createMock(GenericEntityWithRecipes::class);
        /* @var GenericEntityWithRecipes&MockObject $item2 */
        $item2 = $this->createMock(GenericEntityWithRecipes::class);

        /* @var EntityData&MockObject $entityData1 */
        $entityData1 = $this->createMock(EntityData::class);
        /* @var EntityData&MockObject $entityData2 */
        $entityData2 = $this->createMock(EntityData::class);

        $items = [$item1, $item2];
        $entityData = [$entityData1, $entityData2];

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getQueryParams')
                ->willReturn($queryParams);

        /* @var RandomHandler&MockObject $handler */
        $handler = $this->getMockBuilder(RandomHandler::class)
                        ->onlyMethods(['fetchData', 'createEntityData'])
                        ->setConstructorArgs([$this->apiClient, $this->mapperManager, 21])
                        ->getMock();
        $handler->expects($this->once())
                ->method('fetchData')
                ->with($this->identicalTo($numberOfResults))
                ->willReturn($items);
        $handler->expects($this->exactly(2))
                ->method('createEntityData')
                ->withConsecutive(
                    [$this->identicalTo($item1)],
                    [$this->identicalTo($item2)]
                )
                ->willReturnOnConsecutiveCalls(
                    $entityData1,
                    $entityData2
                );

        /* @var TransferResponse $result */
        $result = $handler->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        $this->assertSame($entityData, $result->getTransfer());
    }

    /**
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchData(): void
    {
        $numberOfResults = 42;
        $numberOfRecipesPerResult = 21;

        $expectedRequest = new ItemRandomRequest();
        $expectedRequest->setNumberOfResults($numberOfResults)
                        ->setNumberOfRecipesPerResult($numberOfRecipesPerResult);

        $items = [
            $this->createMock(GenericEntityWithRecipes::class),
            $this->createMock(GenericEntityWithRecipes::class),
        ];

        /* @var ItemRandomResponse&MockObject $response */
        $response = $this->createMock(ItemRandomResponse::class);
        $response->expects($this->once())
                 ->method('getItems')
                 ->willReturn($items);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willReturn($response);

        $handler = new RandomHandler($this->apiClient, $this->mapperManager, $numberOfRecipesPerResult);
        $result = $this->invokeMethod($handler, 'fetchData', $numberOfResults);

        $this->assertSame($items, $result);
    }

    /**
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchDataWithException(): void
    {
        $numberOfResults = 42;
        $numberOfRecipesPerResult = 21;

        $expectedRequest = new ItemRandomRequest();
        $expectedRequest->setNumberOfResults($numberOfResults)
                        ->setNumberOfRecipesPerResult($numberOfRecipesPerResult);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willThrowException($this->createMock(ApiClientException::class));

        $this->expectException(FailedApiRequestException::class);

        $handler = new RandomHandler($this->apiClient, $this->mapperManager, $numberOfRecipesPerResult);
        $this->invokeMethod($handler, 'fetchData', $numberOfResults);
    }

    /**
     * Tests the createEntityData method.
     * @throws ReflectionException
     * @covers ::createEntityData
     */
    public function testCreateEntityData(): void
    {
        /* @var GenericEntityWithRecipes&MockObject $item */
        $item = $this->createMock(GenericEntityWithRecipes::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($item), $this->isInstanceOf(EntityData::class));

        $handler = new RandomHandler($this->apiClient, $this->mapperManager, 42);
        $this->invokeMethod($handler, 'createEntityData', $item);
    }

    /**
     * Tests the createEntityData method.
     * @throws ReflectionException
     * @covers ::createEntityData
     */
    public function testCreateEntityDataWithException(): void
    {
        /* @var GenericEntityWithRecipes&MockObject $item */
        $item = $this->createMock(GenericEntityWithRecipes::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($item), $this->isInstanceOf(EntityData::class))
                            ->willThrowException($this->createMock(MapperException::class));

        $this->expectException(MappingException::class);

        $handler = new RandomHandler($this->apiClient, $this->mapperManager, 42);
        $this->invokeMethod($handler, 'createEntityData', $item);
    }
}
