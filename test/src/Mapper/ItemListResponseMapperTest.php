<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Response\Item\ItemListResponse;
use FactorioItemBrowser\PortalApi\Server\Mapper\ItemListResponseMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemListData;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemMetaData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ItemListResponseMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Mapper\ItemListResponseMapper
 */
class ItemListResponseMapperTest extends TestCase
{
    /**
     * @param array<string> $mockedMethods
     * @return ItemListResponseMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): ItemListResponseMapper
    {
        return $this->getMockBuilder(ItemListResponseMapper::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->getMock();
    }

    public function testSupports(): void
    {
        $instance = $this->createInstance();

        $this->assertSame(ItemListResponse::class, $instance->getSupportedSourceClass());
        $this->assertSame(ItemListData::class, $instance->getSupportedDestinationClass());
    }

    public function testMap(): void
    {
        $item1 = new GenericEntityWithRecipes();
        $item1->type = 'abc';
        $item1->name = 'def';
        $item2 = new GenericEntityWithRecipes();
        $item2->type = 'ghi';
        $item2->name = 'jkl';

        $itemData1 = new ItemMetaData();
        $itemData1->type = 'abc';
        $itemData1->name = 'def';
        $itemData2 = new ItemMetaData();
        $itemData2->type = 'ghi';
        $itemData2->name = 'jkl';

        $source = new ItemListResponse();
        $source->items = [$item1, $item2];
        $source->totalNumberOfResults = 42;

        $expectedDestination = new ItemListData();
        $expectedDestination->results = [$itemData1, $itemData2];
        $expectedDestination->numberOfResults = 42;

        $destination = new ItemListData();

        $instance = $this->createInstance();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
