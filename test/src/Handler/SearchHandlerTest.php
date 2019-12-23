<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Search\SearchQueryRequest;
use FactorioItemBrowser\Api\Client\Response\Search\SearchQueryResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\SearchHandler;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SearchResultsData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

/**
 * The PHPUnit test of the SearchHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\SearchHandler
 */
class SearchHandlerTest extends TestCase
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

        $handler = new SearchHandler($this->apiClient, $this->mapperManager, $numberOfRecipesPerResult);

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
        $queryParams = [
            'query' => 'abc',
            'indexOfFirstResult' => '42',
            'numberOfResults' => '21',
        ];

        /* @var SearchQueryResponse&MockObject $searchQueryResponse */
        $searchQueryResponse = $this->createMock(SearchQueryResponse::class);
        /* @var SearchResultsData&MockObject $searchResultsData */
        $searchResultsData = $this->createMock(SearchResultsData::class);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getQueryParams')
                ->willReturn($queryParams);

        /* @var SearchHandler&MockObject $handler */
        $handler = $this->getMockBuilder(SearchHandler::class)
                        ->onlyMethods(['fetchData', 'createSearchResultsData'])
                        ->setConstructorArgs([$this->apiClient, $this->mapperManager, 1337])
                        ->getMock();
        $handler->expects($this->once())
                ->method('fetchData')
                ->with($this->identicalTo('abc'), $this->identicalTo(42), $this->identicalTo(21))
                ->willReturn($searchQueryResponse);
        $handler->expects($this->once())
                ->method('createSearchResultsData')
                ->with($this->identicalTo($searchQueryResponse), $this->identicalTo('abc'))
                ->willReturn($searchResultsData);

        /* @var TransferResponse $result */
        $result = $handler->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        $this->assertSame($searchResultsData, $result->getTransfer());
    }

    /**
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchData(): void
    {
        $query = 'abc';
        $indexOfFirstResult = 42;
        $numberOfResults = 21;
        $numberOfRecipesPerResult = 1337;

        $expectedRequest = new SearchQueryRequest();
        $expectedRequest->setQuery('abc')
                        ->setIndexOfFirstResult(42)
                        ->setNumberOfResults(21)
                        ->setNumberOfRecipesPerResult(1337);

        /* @var SearchQueryResponse&MockObject $response */
        $response = $this->createMock(SearchQueryResponse::class);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willReturn($response);

        $handler = new SearchHandler($this->apiClient, $this->mapperManager, $numberOfRecipesPerResult);
        $result = $this->invokeMethod($handler, 'fetchData', $query, $indexOfFirstResult, $numberOfResults);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchDataWithException(): void
    {
        $query = 'abc';
        $indexOfFirstResult = 42;
        $numberOfResults = 21;
        $numberOfRecipesPerResult = 1337;

        $expectedRequest = new SearchQueryRequest();
        $expectedRequest->setQuery('abc')
                        ->setIndexOfFirstResult(42)
                        ->setNumberOfResults(21)
                        ->setNumberOfRecipesPerResult(1337);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willThrowException($this->createMock(ApiClientException::class));

        $this->expectException(FailedApiRequestException::class);

        $handler = new SearchHandler($this->apiClient, $this->mapperManager, $numberOfRecipesPerResult);
        $this->invokeMethod($handler, 'fetchData', $query, $indexOfFirstResult, $numberOfResults);
    }

    /**
     * Tests the createSearchResultsData method.
     * @throws ReflectionException
     * @covers ::createSearchResultsData
     */
    public function testCreateSearchResultsData(): void
    {
        $query = 'abc';

        $expectedData = new SearchResultsData();
        $expectedData->setQuery($query);

        /* @var SearchQueryResponse&MockObject $searchQueryResponse */
        $searchQueryResponse = $this->createMock(SearchQueryResponse::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($searchQueryResponse), $this->equalTo($expectedData));

        $handler = new SearchHandler($this->apiClient, $this->mapperManager, 42);
        $result = $this->invokeMethod($handler, 'createSearchResultsData', $searchQueryResponse, $query);

        $this->assertEquals($expectedData, $result);
    }

    /**
     * Tests the createSearchResultsData method.
     * @throws ReflectionException
     * @covers ::createSearchResultsData
     */
    public function testCreateSearchResultsDataWithException(): void
    {
        $query = 'abc';

        $expectedData = new SearchResultsData();
        $expectedData->setQuery($query);

        /* @var SearchQueryResponse&MockObject $searchQueryResponse */
        $searchQueryResponse = $this->createMock(SearchQueryResponse::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($searchQueryResponse), $this->equalTo($expectedData))
                            ->willThrowException($this->createMock(MapperException::class));

        $this->expectException(MappingException::class);

        $handler = new SearchHandler($this->apiClient, $this->mapperManager, 42);
        $this->invokeMethod($handler, 'createSearchResultsData', $searchQueryResponse, $query);
    }
}
