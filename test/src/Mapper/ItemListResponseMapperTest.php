<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Response\Item\ItemListResponse;
use FactorioItemBrowser\PortalApi\Server\Mapper\ItemListResponseMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemListData;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemMetaData;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ItemListResponseMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Mapper\ItemListResponseMapper
 */
class ItemListResponseMapperTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the getSupportedSourceClass method.
     * @covers ::getSupportedSourceClass
     */
    public function testGetSupportedSourceClass(): void
    {
        $expectedResult = ItemListResponse::class;

        $mapper = new ItemListResponseMapper();
        $result = $mapper->getSupportedSourceClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getSupportedDestinationClass method.
     * @covers ::getSupportedDestinationClass
     */
    public function testGetSupportedDestinationClass(): void
    {
        $expectedResult = ItemListData::class;

        $mapper = new ItemListResponseMapper();
        $result = $mapper->getSupportedDestinationClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the map method.
     * @covers ::map
     */
    public function testMap(): void
    {
        $item1 = $this->createMock(GenericEntityWithRecipes::class);
        $item2 = $this->createMock(GenericEntityWithRecipes::class);
        $itemData1 = $this->createMock(ItemMetaData::class);
        $itemData2 = $this->createMock(ItemMetaData::class);

        $response = new ItemListResponse();
        $response->setItems([$item1, $item2])
                 ->setTotalNumberOfResults(42);

        $expectedResult = new ItemListData();
        $expectedResult->setResults([$itemData1, $itemData2])
                       ->setNumberOfResults(42);

        $mapper = $this->getMockBuilder(ItemListResponseMapper::class)
                       ->onlyMethods(['mapItem'])
                       ->getMock();
        $mapper->expects($this->exactly(2))
               ->method('mapItem')
               ->withConsecutive(
                   [$this->identicalTo($item1)],
                   [$this->identicalTo($item2)],
               )
               ->willReturnOnConsecutiveCalls(
                   $itemData1,
                   $itemData2,
               );

        $result = new ItemListData();
        $mapper->map($response, $result);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the mapItem method.
     * @throws ReflectionException
     * @covers ::mapItem
     */
    public function testMapItem(): void
    {
        $item = new GenericEntity();
        $item->setType('abc')
             ->setName('def');

        $expectedResult = new ItemMetaData();
        $expectedResult->setType('abc')
                       ->setName('def');

        $mapper = new ItemListResponseMapper();
        $result = $this->invokeMethod($mapper, 'mapItem', $item);

        $this->assertEquals($expectedResult, $result);
    }
}
