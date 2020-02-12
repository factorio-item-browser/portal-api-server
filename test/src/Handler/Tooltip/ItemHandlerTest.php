<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Tooltip;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Exception\NotFoundException;
use FactorioItemBrowser\Api\Client\Request\Item\ItemProductRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemProductResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
use FactorioItemBrowser\PortalApi\Server\Handler\Tooltip\ItemHandler;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

/**
 * The PHPUnit test of the ItemHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Tooltip\ItemHandler
 */
class ItemHandlerTest extends TestCase
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

        $handler = new ItemHandler($this->apiClient, $this->mapperManager, $numberOfRecipesPerResult);

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
        $type = 'abc';
        $name = 'def';

        /* @var GenericEntityWithRecipes&MockObject $item */
        $item = $this->createMock(GenericEntityWithRecipes::class);
        /* @var EntityData&MockObject $entityData */
        $entityData = $this->createMock(EntityData::class);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->exactly(2))
                ->method('getAttribute')
                ->withConsecutive(
                    [$this->identicalTo('type'), $this->identicalTo('')],
                    [$this->identicalTo('name'), $this->identicalTo('')]
                )
                ->willReturnOnConsecutiveCalls(
                    $type,
                    $name
                );

        /* @var ItemHandler&MockObject $handler */
        $handler = $this->getMockBuilder(ItemHandler::class)
                        ->onlyMethods(['fetchData', 'createEntityData'])
                        ->setConstructorArgs([$this->apiClient, $this->mapperManager, 42])
                        ->getMock();
        $handler->expects($this->once())
                ->method('fetchData')
                ->with($this->identicalTo($type), $this->identicalTo($name))
                ->willReturn($item);
        $handler->expects($this->once())
                ->method('createEntityData')
                ->with($this->identicalTo($item))
                ->willReturn($entityData);

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
        $numberOfRecipesPerResult = 42;
        $type = 'abc';
        $name = 'def';

        $expectedRequest = new ItemProductRequest();
        $expectedRequest->setType($type)
                        ->setName($name)
                        ->setIndexOfFirstResult(0)
                        ->setNumberOfResults($numberOfRecipesPerResult);

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

        $handler = new ItemHandler($this->apiClient, $this->mapperManager, $numberOfRecipesPerResult);
        $result = $this->invokeMethod($handler, 'fetchData', $type, $name);

        $this->assertSame($item, $result);
    }

    /**
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchDataWithNotFoundException(): void
    {
        $numberOfRecipesPerResult = 42;
        $type = 'abc';
        $name = 'def';

        $expectedRequest = new ItemProductRequest();
        $expectedRequest->setType($type)
                        ->setName($name)
                        ->setIndexOfFirstResult(0)
                        ->setNumberOfResults($numberOfRecipesPerResult);


        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willThrowException($this->createMock(NotFoundException::class));

        $this->expectException(UnknownEntityException::class);

        $handler = new ItemHandler($this->apiClient, $this->mapperManager, $numberOfRecipesPerResult);
        $this->invokeMethod($handler, 'fetchData', $type, $name);
    }

    /**
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchDataWithApiException(): void
    {
        $numberOfRecipesPerResult = 42;
        $type = 'abc';
        $name = 'def';

        $expectedRequest = new ItemProductRequest();
        $expectedRequest->setType($type)
                        ->setName($name)
                        ->setIndexOfFirstResult(0)
                        ->setNumberOfResults($numberOfRecipesPerResult);


        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willThrowException($this->createMock(ApiClientException::class));

        $this->expectException(FailedApiRequestException::class);

        $handler = new ItemHandler($this->apiClient, $this->mapperManager, $numberOfRecipesPerResult);
        $this->invokeMethod($handler, 'fetchData', $type, $name);
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

        $handler = new ItemHandler($this->apiClient, $this->mapperManager, 42);
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

        $handler = new ItemHandler($this->apiClient, $this->mapperManager, 42);
        $this->invokeMethod($handler, 'createEntityData', $item);
    }
}
