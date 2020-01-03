<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Item;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
use FactorioItemBrowser\PortalApi\Server\Handler\Item\AbstractRecipesHandler;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemRecipesData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

/**
 * The PHPUnit test of the AbstractRecipesHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Item\AbstractRecipesHandler
 */
class AbstractRecipesHandlerTest extends TestCase
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
        /* @var AbstractRecipesHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractRecipesHandler::class)
                        ->setConstructorArgs([$this->apiClient, $this->mapperManager])
                        ->getMockForAbstractClass();

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
        $type = 'abc';
        $name = 'def';
        $indexOfFirstResult = 42;
        $numberOfResults = 21;

        $queryParams = [
            'indexOfFirstResult' => '42',
            'numberOfResults' => '21',
        ];

        /* @var GenericEntityWithRecipes&MockObject $item */
        $item = $this->createMock(GenericEntityWithRecipes::class);
        /* @var ItemRecipesData&MockObject $itemRecipesData */
        $itemRecipesData = $this->createMock(ItemRecipesData::class);

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
        $request->expects($this->once())
                ->method('getQueryParams')
                ->willReturn($queryParams);

        /* @var AbstractRecipesHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractRecipesHandler::class)
                        ->onlyMethods(['fetchData', 'createItemRecipesData'])
                        ->setConstructorArgs([$this->apiClient, $this->mapperManager])
                        ->getMockForAbstractClass();
        $handler->expects($this->once())
                ->method('fetchData')
                ->with(
                    $this->identicalTo($type),
                    $this->identicalTo($name),
                    $this->identicalTo($indexOfFirstResult),
                    $this->identicalTo($numberOfResults)
                )
                ->willReturn($item);
        $handler->expects($this->once())
                ->method('createItemRecipesData')
                ->with($this->identicalTo($item))
                ->willReturn($itemRecipesData);

        /* @var TransferResponse $result */
        $result = $handler->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        $this->assertSame($itemRecipesData, $result->getTransfer());
    }

    /**
     * Tests the handle method.
     * @throws PortalApiServerException
     * @covers ::handle
     */
    public function testHandleWithoutItem(): void
    {
        $type = 'abc';
        $name = 'def';
        $indexOfFirstResult = 42;
        $numberOfResults = 21;

        $queryParams = [
            'indexOfFirstResult' => '42',
            'numberOfResults' => '21',
        ];

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
        $request->expects($this->once())
                ->method('getQueryParams')
                ->willReturn($queryParams);

        $this->expectException(UnknownEntityException::class);

        /* @var AbstractRecipesHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractRecipesHandler::class)
                        ->onlyMethods(['fetchData', 'createItemRecipesData'])
                        ->setConstructorArgs([$this->apiClient, $this->mapperManager])
                        ->getMockForAbstractClass();
        $handler->expects($this->once())
                ->method('fetchData')
                ->with(
                    $this->identicalTo($type),
                    $this->identicalTo($name),
                    $this->identicalTo($indexOfFirstResult),
                    $this->identicalTo($numberOfResults)
                )
                ->willReturn(null);
        $handler->expects($this->never())
                ->method('createItemRecipesData');

        $handler->handle($request);
    }

    /**
     * Tests the createItemRecipesData method.
     * @throws ReflectionException
     * @covers ::createItemRecipesData
     */
    public function testCreateItemRecipesData(): void
    {
        $expectedData = new ItemRecipesData();

        /* @var GenericEntityWithRecipes&MockObject $item */
        $item = $this->createMock(GenericEntityWithRecipes::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($item), $this->equalTo($expectedData));

        /* @var AbstractRecipesHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractRecipesHandler::class)
                        ->setConstructorArgs([$this->apiClient, $this->mapperManager])
                        ->getMockForAbstractClass();
        $result = $this->invokeMethod($handler, 'createItemRecipesData', $item);

        $this->assertEquals($expectedData, $result);
    }

    /**
     * Tests the createItemRecipesData method.
     * @throws ReflectionException
     * @covers ::createItemRecipesData
     */
    public function testCreateItemRecipesDataWithException(): void
    {
        $expectedData = new ItemRecipesData();

        /* @var GenericEntityWithRecipes&MockObject $item */
        $item = $this->createMock(GenericEntityWithRecipes::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($item), $this->equalTo($expectedData))
                            ->willThrowException($this->createMock(MapperException::class));

        $this->expectException(MappingException::class);

        /* @var AbstractRecipesHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractRecipesHandler::class)
                        ->setConstructorArgs([$this->apiClient, $this->mapperManager])
                        ->getMockForAbstractClass();
        $this->invokeMethod($handler, 'createItemRecipesData', $item);
    }
}
