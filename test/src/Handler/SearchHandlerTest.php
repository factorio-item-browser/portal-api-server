<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Request\Search\SearchQueryRequest;
use FactorioItemBrowser\Api\Client\Response\Search\SearchQueryResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\SearchHandler;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SearchResultsData;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The PHPUnit test of the SearchHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Handler\SearchHandler
 */
class SearchHandlerTest extends TestCase
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
     * @return SearchHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): SearchHandler
    {
        return $this->getMockBuilder(SearchHandler::class)
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
        $queryParams = [
            'query' => 'abc',
            'indexOfFirstResult' => '42',
            'numberOfResults' => '21',
        ];

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getQueryParams')
                ->willReturn($queryParams);

        $expectedApiRequest = new SearchQueryRequest();
        $expectedApiRequest->query = 'abc';
        $expectedApiRequest->indexOfFirstResult = 42;
        $expectedApiRequest->numberOfResults = 21;
        $expectedApiRequest->numberOfRecipesPerResult = 7;

        $apiResponse = $this->createMock(SearchQueryResponse::class);
        $transfer = new SearchResultsData();
        $transfer->numberOfResults = 1337;
        $expectedTransfer = new SearchResultsData();
        $expectedTransfer->query = 'abc';
        $expectedTransfer->numberOfResults = 1337;

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willReturn(new FulfilledPromise($apiResponse));

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($apiResponse), $this->isInstanceOf(SearchResultsData::class))
                            ->willReturn($transfer);

        $this->numberOfRecipesPerResult = 7;

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        /* @var TransferResponse $result */
        $this->assertEquals($expectedTransfer, $result->getTransfer());
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithApiException(): void
    {
        $queryParams = [
            'query' => 'abc',
            'indexOfFirstResult' => '42',
            'numberOfResults' => '21',
        ];

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getQueryParams')
                ->willReturn($queryParams);

        $expectedApiRequest = new SearchQueryRequest();
        $expectedApiRequest->query = 'abc';
        $expectedApiRequest->indexOfFirstResult = 42;
        $expectedApiRequest->numberOfResults = 21;
        $expectedApiRequest->numberOfRecipesPerResult = 7;

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
