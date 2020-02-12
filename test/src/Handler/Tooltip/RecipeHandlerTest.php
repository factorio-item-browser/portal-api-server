<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Tooltip;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\Recipe;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeDetailsResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
use FactorioItemBrowser\PortalApi\Server\Handler\Tooltip\RecipeHandler;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

/**
 * The PHPUnit test of the RecipeHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Tooltip\RecipeHandler
 */
class RecipeHandlerTest extends TestCase
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
        $handler = new RecipeHandler($this->apiClient, $this->mapperManager);

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

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo('name'), $this->identicalTo(''))
                ->willReturn($name);

        /* @var Recipe&MockObject $recipe */
        $recipe = $this->createMock(Recipe::class);
        /* @var EntityData&MockObject $entityData */
        $entityData = $this->createMock(EntityData::class);

        /* @var RecipeHandler&MockObject $handler */
        $handler = $this->getMockBuilder(RecipeHandler::class)
                        ->onlyMethods(['fetchData', 'createEntityData'])
                        ->setConstructorArgs([$this->apiClient, $this->mapperManager])
                        ->getMock();
        $handler->expects($this->once())
                ->method('fetchData')
                ->with($this->identicalTo($name))
                ->willReturn($recipe);
        $handler->expects($this->once())
                ->method('createEntityData')
                ->with($this->identicalTo($recipe))
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
        $name = 'abc';

        $expectedRequest = new RecipeDetailsRequest();
        $expectedRequest->setNames([$name]);

        $recipe1 = new Recipe();
        $recipe1->setName('def');
        $recipe2 = new Recipe();
        $recipe2->setName('abc');
        $recipes = [$recipe1, $recipe2];

        /* @var RecipeDetailsResponse&MockObject $response */
        $response = $this->createMock(RecipeDetailsResponse::class);
        $response->expects($this->once())
                 ->method('getRecipes')
                 ->willReturn($recipes);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willReturn($response);

        $handler = new RecipeHandler($this->apiClient, $this->mapperManager);
        $result = $this->invokeMethod($handler, 'fetchData', $name);

        $this->assertSame($recipe2, $result);
    }

    /**
     * Tests the fetchData method.
     * @throws ReflectionException
     * @covers ::fetchData
     */
    public function testFetchDataWithoutRecipes(): void
    {
        $name = 'abc';

        $expectedRequest = new RecipeDetailsRequest();
        $expectedRequest->setNames([$name]);

        /* @var RecipeDetailsResponse&MockObject $response */
        $response = $this->createMock(RecipeDetailsResponse::class);
        $response->expects($this->once())
                 ->method('getRecipes')
                 ->willReturn([]);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willReturn($response);

        $this->expectException(UnknownEntityException::class);

        $handler = new RecipeHandler($this->apiClient, $this->mapperManager);
        $this->invokeMethod($handler, 'fetchData', $name);
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
        $expectedRequest->setNames([$name]);

        $this->apiClient->expects($this->once())
                        ->method('fetchResponse')
                        ->with($this->equalTo($expectedRequest))
                        ->willThrowException($this->createMock(ApiClientException::class));

        $this->expectException(FailedApiRequestException::class);

        $handler = new RecipeHandler($this->apiClient, $this->mapperManager);
        $this->invokeMethod($handler, 'fetchData', $name);
    }

    /**
     * Tests the createEntityData method.
     * @throws ReflectionException
     * @covers ::createEntityData
     */
    public function testCreateEntityData(): void
    {
        /* @var Recipe&MockObject $recipe */
        $recipe = $this->createMock(Recipe::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($recipe), $this->isInstanceOf(EntityData::class));

        $handler = new RecipeHandler($this->apiClient, $this->mapperManager);
        $this->invokeMethod($handler, 'createEntityData', $recipe);
    }

    /**
     * Tests the createEntityData method.
     * @throws ReflectionException
     * @covers ::createEntityData
     */
    public function testCreateEntityDataWithException(): void
    {
        /* @var Recipe&MockObject $recipe */
        $recipe = $this->createMock(Recipe::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($recipe), $this->isInstanceOf(EntityData::class))
                            ->willThrowException($this->createMock(MapperException::class));

        $this->expectException(MappingException::class);

        $handler = new RecipeHandler($this->apiClient, $this->mapperManager);
        $this->invokeMethod($handler, 'createEntityData', $recipe);
    }
}
