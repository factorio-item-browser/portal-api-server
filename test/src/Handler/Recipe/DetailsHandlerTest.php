<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Recipe;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeDetailsResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
use FactorioItemBrowser\PortalApi\Server\Handler\Recipe\DetailsHandler;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeDetailsData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

/**
 * The PHPUnit test of the DetailsHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Recipe\DetailsHandler
 */
class DetailsHandlerTest extends TestCase
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
        $handler = new DetailsHandler($this->apiClient, $this->mapperManager);

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

        /* @var RecipeWithExpensiveVersion&MockObject $recipe */
        $recipe = $this->createMock(RecipeWithExpensiveVersion::class);
        /* @var RecipeDetailsData&MockObject $recipeDetailsData */
        $recipeDetailsData = $this->createMock(RecipeDetailsData::class);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo('name'), $this->identicalTo(''))
                ->willReturn($name);

        /* @var DetailsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(DetailsHandler::class)
                        ->onlyMethods(['fetchData', 'createRecipeDetailsData'])
                        ->setConstructorArgs([$this->apiClient, $this->mapperManager])
                        ->getMock();
        $handler->expects($this->once())
                ->method('fetchData')
                ->with($this->identicalTo($name))
                ->willReturn($recipe);
        $handler->expects($this->once())
                ->method('createRecipeDetailsData')
                ->with($this->identicalTo($recipe))
                ->willReturn($recipeDetailsData);

        /* @var TransferResponse $result */
        $result = $handler->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        $this->assertSame($recipeDetailsData, $result->getTransfer());
    }

    /**
     * Tests the handle method.
     * @throws PortalApiServerException
     * @covers ::handle
     */
    public function testHandleWithoutRecipe(): void
    {
        $name = 'abc';

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo('name'), $this->identicalTo(''))
                ->willReturn($name);

        $this->expectException(UnknownEntityException::class);

        /* @var DetailsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(DetailsHandler::class)
                        ->onlyMethods(['fetchData', 'createRecipeDetailsData'])
                        ->setConstructorArgs([$this->apiClient, $this->mapperManager])
                        ->getMock();
        $handler->expects($this->once())
                ->method('fetchData')
                ->with($this->identicalTo($name))
                ->willReturn(null);
        $handler->expects($this->never())
                ->method('createRecipeDetailsData');

        /* @var TransferResponse $result */
        $handler->handle($request);
    }

    /**
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchData(): void
    {
        $name = 'abc';

        $expectedRequest = new RecipeDetailsRequest();
        $expectedRequest->setNames(['abc']);

        $recipe1 = new RecipeWithExpensiveVersion();
        $recipe1->setName('def');
        $recipe2 = new RecipeWithExpensiveVersion();
        $recipe2->setName('abc');

        $response = new RecipeDetailsResponse();
        $response->setRecipes([$recipe1, $recipe2]);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willReturn($response);

        $handler = new DetailsHandler($this->apiClient, $this->mapperManager);
        $result = $this->invokeMethod($handler, 'fetchData', $name);

        $this->assertSame($recipe2, $result);
    }

    /**
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchDataWithoutRecipe(): void
    {
        $name = 'abc';

        $expectedRequest = new RecipeDetailsRequest();
        $expectedRequest->setNames(['abc']);

        $response = new RecipeDetailsResponse();
        $response->setRecipes([]);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willReturn($response);

        $handler = new DetailsHandler($this->apiClient, $this->mapperManager);
        $result = $this->invokeMethod($handler, 'fetchData', $name);

        $this->assertNull($result);
    }

    /**
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchDataWithException(): void
    {
        $name = 'abc';

        $expectedRequest = new RecipeDetailsRequest();
        $expectedRequest->setNames(['abc']);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willThrowException($this->createMock(ApiClientException::class));

        $this->expectException(FailedApiRequestException::class);

        $handler = new DetailsHandler($this->apiClient, $this->mapperManager);
        $this->invokeMethod($handler, 'fetchData', $name);
    }

    /**
     * Tests the createRecipeDetailsData method.
     * @throws ReflectionException
     * @covers ::createRecipeDetailsData
     */
    public function testCreateRecipeDetailsData(): void
    {
        /* @var RecipeWithExpensiveVersion&MockObject $recipe */
        $recipe = $this->createMock(RecipeWithExpensiveVersion::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($recipe), $this->isInstanceOf(RecipeDetailsData::class));

        $handler = new DetailsHandler($this->apiClient, $this->mapperManager);
        $this->invokeMethod($handler, 'createRecipeDetailsData', $recipe);
    }

    /**
     * Tests the createRecipeDetailsData method.
     * @throws ReflectionException
     * @covers ::createRecipeDetailsData
     */
    public function testCreateRecipeDetailsDataWithException(): void
    {
        /* @var RecipeWithExpensiveVersion&MockObject $recipe */
        $recipe = $this->createMock(RecipeWithExpensiveVersion::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($recipe), $this->isInstanceOf(RecipeDetailsData::class))
                            ->willThrowException($this->createMock(MapperException::class));

        $this->expectException(MappingException::class);

        $handler = new DetailsHandler($this->apiClient, $this->mapperManager);
        $this->invokeMethod($handler, 'createRecipeDetailsData', $recipe);
    }
}
