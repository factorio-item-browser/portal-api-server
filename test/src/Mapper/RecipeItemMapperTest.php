<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use FactorioItemBrowser\Api\Client\Transfer\Item;
use FactorioItemBrowser\PortalApi\Server\Mapper\RecipeItemMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeItemData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeItemMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Mapper\RecipeItemMapper
 */
class RecipeItemMapperTest extends TestCase
{
    /**
     * @param array<string> $mockedMethods
     * @return RecipeItemMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): RecipeItemMapper
    {
        return $this->getMockBuilder(RecipeItemMapper::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->getMock();
    }

    public function testSupports(): void
    {
        $instance = $this->createInstance();

        $this->assertSame(Item::class, $instance->getSupportedSourceClass());
        $this->assertSame(RecipeItemData::class, $instance->getSupportedDestinationClass());
    }

    public function testMap(): void
    {
        $source = new Item();
        $source->type = 'abc';
        $source->name = 'def';
        $source->label = 'ghi';
        $source->amount = 13.37;

        $expectedDestination = new RecipeItemData();
        $expectedDestination->type = 'abc';
        $expectedDestination->name = 'def';
        $expectedDestination->label = 'ghi';
        $expectedDestination->amount = 13.37;

        $destination = new RecipeItemData();

        $instance = $this->createInstance();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
