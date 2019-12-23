<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Response\Search\SearchQueryResponse;
use FactorioItemBrowser\PortalApi\Server\Mapper\SearchQueryResponseMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SearchResultsData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the SearchQueryResponseMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Mapper\SearchQueryResponseMapper
 */
class SearchQueryResponseMapperTest extends TestCase
{
    use ReflectionTrait;

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

        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * Tests the setMapperManager method.
     * @throws ReflectionException
     * @covers ::setMapperManager
     */
    public function testSetMapperManager(): void
    {
        $mapper = new SearchQueryResponseMapper();
        $mapper->setMapperManager($this->mapperManager);

        $this->assertSame($this->mapperManager, $this->extractProperty($mapper, 'mapperManager'));
    }

    /**
     * Tests the getSupportedSourceClass method.
     * @covers ::getSupportedSourceClass
     */
    public function testGetSupportedSourceClass(): void
    {
        $expectedResult = SearchQueryResponse::class;

        $mapper = new SearchQueryResponseMapper();
        $mapper->setMapperManager($this->mapperManager);

        $result = $mapper->getSupportedSourceClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getSupportedDestinationClass method.
     * @covers ::getSupportedDestinationClass
     */
    public function testGetSupportedDestinationClass(): void
    {
        $expectedResult = SearchResultsData::class;

        $mapper = new SearchQueryResponseMapper();
        $mapper->setMapperManager($this->mapperManager);

        $result = $mapper->getSupportedDestinationClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the map method.
     * @covers ::map
     */
    public function testMap(): void
    {
        /* @var GenericEntityWithRecipes&MockObject $entity1 */
        $entity1 = $this->createMock(GenericEntityWithRecipes::class);
        /* @var GenericEntityWithRecipes&MockObject $entity2 */
        $entity2 = $this->createMock(GenericEntityWithRecipes::class);

        /* @var EntityData&MockObject $entityData1 */
        $entityData1 = $this->createMock(EntityData::class);
        /* @var EntityData&MockObject $entityData2 */
        $entityData2 = $this->createMock(EntityData::class);

        $source = new SearchQueryResponse();
        $source->setResults([$entity1, $entity2])
               ->setTotalNumberOfResults(42);

        $expectedDestination = new SearchResultsData();
        $expectedDestination->setResults([$entityData1, $entityData2])
                            ->setNumberOfResults(42);

        $destination = new SearchResultsData();

        /* @var SearchQueryResponseMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(SearchQueryResponseMapper::class)
                       ->onlyMethods(['mapEntity'])
                       ->getMock();
        $mapper->expects($this->exactly(2))
               ->method('mapEntity')
               ->withConsecutive(
                   [$this->identicalTo($entity1)],
                   [$this->identicalTo($entity2)]
               )
               ->willReturnOnConsecutiveCalls(
                   $entityData1,
                   $entityData2
               );
        $mapper->setMapperManager($this->mapperManager);

        $mapper->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }

    /**
     * Tests the mapEntity method.
     * @throws ReflectionException
     * @covers ::mapEntity
     */
    public function testMapEntity(): void
    {
        /* @var GenericEntityWithRecipes&MockObject $entity */
        $entity = $this->createMock(GenericEntityWithRecipes::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($entity), $this->isInstanceOf(EntityData::class));

        $mapper = new SearchQueryResponseMapper();
        $mapper->setMapperManager($this->mapperManager);

        $this->invokeMethod($mapper, 'mapEntity', $entity);
    }
}
