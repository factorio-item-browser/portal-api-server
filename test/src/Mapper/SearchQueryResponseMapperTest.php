<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Response\Search\SearchQueryResponse;
use FactorioItemBrowser\PortalApi\Server\Mapper\SearchQueryResponseMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SearchResultsData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the SearchQueryResponseMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Mapper\SearchQueryResponseMapper
 */
class SearchQueryResponseMapperTest extends TestCase
{
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;

    protected function setUp(): void
    {
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return SearchQueryResponseMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): SearchQueryResponseMapper
    {
        $instance = $this->getMockBuilder(SearchQueryResponseMapper::class)
                         ->disableProxyingToOriginalMethods()
                         ->onlyMethods($mockedMethods)
                         ->getMock();
        $instance->setMapperManager($this->mapperManager);
        return $instance;
    }

    public function testSupports(): void
    {
        $instance = $this->createInstance();

        $this->assertSame(SearchQueryResponse::class, $instance->getSupportedSourceClass());
        $this->assertSame(SearchResultsData::class, $instance->getSupportedDestinationClass());
    }

    public function testMap(): void
    {
        $entity1 = $this->createMock(GenericEntityWithRecipes::class);
        $entity2 = $this->createMock(GenericEntityWithRecipes::class);
        $entityData1 = $this->createMock(EntityData::class);
        $entityData2 = $this->createMock(EntityData::class);

        $source = new SearchQueryResponse();
        $source->results = [$entity1, $entity2];
        $source->totalNumberOfResults = 42;

        $expectedDestination = new SearchResultsData();
        $expectedDestination->results = [$entityData1, $entityData2];
        $expectedDestination->numberOfResults = 42;

        $destination = new SearchResultsData();

        $this->mapperManager->expects($this->exactly(2))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($entity1), $this->isInstanceOf(EntityData::class)],
                                [$this->identicalTo($entity2), $this->isInstanceOf(EntityData::class)],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $entityData1,
                                $entityData2,
                            );

        $instance = $this->createInstance();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
