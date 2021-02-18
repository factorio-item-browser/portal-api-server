<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Recipe;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Exception\NotFoundException;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeDetailsResponse;
use FactorioItemBrowser\Api\Client\Transfer\RecipeWithExpensiveVersion;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
use FactorioItemBrowser\PortalApi\Server\Handler\Recipe\DetailsHandler;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeDetailsData;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The PHPUnit test of the DetailsHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Handler\Recipe\DetailsHandler
 */
class DetailsHandlerTest extends TestCase
{
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
     * @return DetailsHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): DetailsHandler
    {
        return $this->getMockBuilder(DetailsHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->apiClient,
                        $this->mapperManager,
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
                    ['name', '', 'abc'],
                ]);

        $expectedApiRequest = new RecipeDetailsRequest();
        $expectedApiRequest->names = ['abc'];

        $recipe = new RecipeWithExpensiveVersion();
        $recipe->name = 'abc';

        $apiResponse = new RecipeDetailsResponse();
        $apiResponse->recipes = [$recipe];

        $transfer = $this->createMock(RecipeDetailsData::class);

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willReturn(new FulfilledPromise($apiResponse));

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($recipe), $this->isInstanceOf(RecipeDetailsData::class))
                            ->willReturn($transfer);

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        /* @var TransferResponse $result */
        $this->assertSame($transfer, $result->getTransfer());
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithWrongRecipe(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getAttribute')
                ->willReturnMap([
                    ['name', '', 'abc'],
                ]);

        $expectedApiRequest = new RecipeDetailsRequest();
        $expectedApiRequest->names = ['abc'];

        $recipe = new RecipeWithExpensiveVersion();
        $recipe->name = 'def';

        $apiResponse = new RecipeDetailsResponse();
        $apiResponse->recipes = [$recipe];

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willReturn(new FulfilledPromise($apiResponse));

        $this->mapperManager->expects($this->never())
                            ->method('map');

        $this->expectException(UnknownEntityException::class);

        $instance = $this->createInstance();
        $instance->handle($request);
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithoutRecipe(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getAttribute')
                ->willReturnMap([
                    ['name', '', 'abc'],
                ]);

        $expectedApiRequest = new RecipeDetailsRequest();
        $expectedApiRequest->names = ['abc'];

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willThrowException($this->createMock(NotFoundException::class));

        $this->mapperManager->expects($this->never())
                            ->method('map');

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
                    ['name', '', 'abc'],
                ]);

        $expectedApiRequest = new RecipeDetailsRequest();
        $expectedApiRequest->names = ['abc'];

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willThrowException($this->createMock(ClientException::class));

        $this->mapperManager->expects($this->never())
                            ->method('map');

        $this->expectException(FailedApiRequestException::class);

        $instance = $this->createInstance();
        $instance->handle($request);
    }
}
