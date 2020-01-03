<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Recipe;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Exception\NotFoundException;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeMachinesRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeMachinesResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
use FactorioItemBrowser\PortalApi\Server\Handler\Recipe\MachinesHandler;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeMachinesData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

/**
 * The PHPUnit test of the MachinesHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Recipe\MachinesHandler
 */
class MachinesHandlerTest extends TestCase
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
        $handler = new MachinesHandler($this->apiClient, $this->mapperManager);

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
        $name = 'abc';
        $indexOfFirstResult = 42;
        $numberOfResults = 21;
        $queryParams = [
            'indexOfFirstResult' => '42',
            'numberOfResults' => '21',
        ];

        /* @var RecipeMachinesResponse&MockObject $machinesResponse */
        $machinesResponse = $this->createMock(RecipeMachinesResponse::class);
        /* @var RecipeMachinesData&MockObject $recipeMachinesData */
        $recipeMachinesData = $this->createMock(RecipeMachinesData::class);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo('name'), $this->identicalTo(''))
                ->willReturn($name);
        $request->expects($this->once())
                ->method('getQueryParams')
                ->willReturn($queryParams);

        /* @var MachinesHandler&MockObject $handler */
        $handler = $this->getMockBuilder(MachinesHandler::class)
                        ->onlyMethods(['fetchData', 'createRecipeMachinesData'])
                        ->setConstructorArgs([$this->apiClient, $this->mapperManager])
                        ->getMock();
        $handler->expects($this->once())
                ->method('fetchData')
                ->with(
                    $this->identicalTo($name),
                    $this->identicalTo($indexOfFirstResult),
                    $this->identicalTo($numberOfResults)
                )
                ->willReturn($machinesResponse);
        $handler->expects($this->once())
                ->method('createRecipeMachinesData')
                ->with($this->identicalTo($machinesResponse))
                ->willReturn($recipeMachinesData);

        /* @var TransferResponse $result */
        $result = $handler->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        $this->assertSame($recipeMachinesData, $result->getTransfer());
    }

    /**
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchData(): void
    {
        $name = 'abc';
        $indexOfFirstResult = 42;
        $numberOfResults = 21;

        $expectedRequest = new RecipeMachinesRequest();
        $expectedRequest->setName('abc')
                        ->setIndexOfFirstResult(42)
                        ->setNumberOfResults(21);

        /* @var RecipeMachinesResponse&MockObject $response */
        $response = $this->createMock(RecipeMachinesResponse::class);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willReturn($response);

        $handler = new MachinesHandler($this->apiClient, $this->mapperManager);
        $result = $this->invokeMethod($handler, 'fetchData', $name, $indexOfFirstResult, $numberOfResults);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchDataWithNotFoundException(): void
    {
        $name = 'abc';
        $indexOfFirstResult = 42;
        $numberOfResults = 21;

        $expectedRequest = new RecipeMachinesRequest();
        $expectedRequest->setName('abc')
                        ->setIndexOfFirstResult(42)
                        ->setNumberOfResults(21);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willThrowException($this->createMock(NotFoundException::class));

        $this->expectException(UnknownEntityException::class);

        $handler = new MachinesHandler($this->apiClient, $this->mapperManager);
        $this->invokeMethod($handler, 'fetchData', $name, $indexOfFirstResult, $numberOfResults);
    }

    /**
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchDataWithException(): void
    {
        $name = 'abc';
        $indexOfFirstResult = 42;
        $numberOfResults = 21;

        $expectedRequest = new RecipeMachinesRequest();
        $expectedRequest->setName('abc')
                        ->setIndexOfFirstResult(42)
                        ->setNumberOfResults(21);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willThrowException($this->createMock(ApiClientException::class));

        $this->expectException(FailedApiRequestException::class);

        $handler = new MachinesHandler($this->apiClient, $this->mapperManager);
        $this->invokeMethod($handler, 'fetchData', $name, $indexOfFirstResult, $numberOfResults);
    }

    /**
     * Tests the createRecipeMachinesData method.
     * @throws ReflectionException
     * @covers ::createRecipeMachinesData
     */
    public function testCreateRecipeMachinesData(): void
    {
        /* @var RecipeMachinesResponse&MockObject $machinesResponse */
        $machinesResponse = $this->createMock(RecipeMachinesResponse::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with(
                                $this->identicalTo($machinesResponse),
                                $this->isInstanceOf(RecipeMachinesData::class)
                            );

        $handler = new MachinesHandler($this->apiClient, $this->mapperManager);
        $this->invokeMethod($handler, 'createRecipeMachinesData', $machinesResponse);
    }

    /**
     * Tests the createRecipeMachinesData method.
     * @throws ReflectionException
     * @covers ::createRecipeMachinesData
     */
    public function testCreateRecipeMachinesDataWithException(): void
    {
        /* @var RecipeMachinesResponse&MockObject $machinesResponse */
        $machinesResponse = $this->createMock(RecipeMachinesResponse::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with(
                                $this->identicalTo($machinesResponse),
                                $this->isInstanceOf(RecipeMachinesData::class)
                            )
                            ->willThrowException($this->createMock(MapperException::class));

        $this->expectException(MappingException::class);

        $handler = new MachinesHandler($this->apiClient, $this->mapperManager);
        $this->invokeMethod($handler, 'createRecipeMachinesData', $machinesResponse);
    }
}
