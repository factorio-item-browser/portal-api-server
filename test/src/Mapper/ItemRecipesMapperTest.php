<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Transfer\RecipeWithExpensiveVersion;
use FactorioItemBrowser\PortalApi\Server\Mapper\ItemRecipesMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemRecipesData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ItemRecipesMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Mapper\ItemRecipesMapper
 */
class ItemRecipesMapperTest extends TestCase
{
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;

    protected function setUp(): void
    {
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return ItemRecipesMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): ItemRecipesMapper
    {
        $instance = $this->getMockBuilder(ItemRecipesMapper::class)
                         ->disableProxyingToOriginalMethods()
                         ->onlyMethods($mockedMethods)
                         ->getMock();
        $instance->setMapperManager($this->mapperManager);
        return $instance;
    }

    public function testSupports(): void
    {
        $instance = $this->createInstance();

        $this->assertSame(GenericEntityWithRecipes::class, $instance->getSupportedSourceClass());
        $this->assertSame(ItemRecipesData::class, $instance->getSupportedDestinationClass());
    }

    public function testMap(): void
    {
        $recipe1 = $this->createMock(RecipeWithExpensiveVersion::class);
        $recipe2 = $this->createMock(RecipeWithExpensiveVersion::class);
        $result1 = $this->createMock(EntityData::class);
        $result2 = $this->createMock(EntityData::class);

        $source = new GenericEntityWithRecipes();
        $source->type = 'abc';
        $source->name = 'def';
        $source->label = 'ghi';
        $source->description = 'jkl';
        $source->recipes = [$recipe1, $recipe2];
        $source->totalNumberOfRecipes = 42;

        $expectedDestination = new ItemRecipesData();
        $expectedDestination->type = 'abc';
        $expectedDestination->name = 'def';
        $expectedDestination->label = 'ghi';
        $expectedDestination->description = 'jkl';
        $expectedDestination->results = [$result1, $result2];
        $expectedDestination->numberOfResults = 42;

        $destination = new ItemRecipesData();

        $this->mapperManager->expects($this->exactly(2))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($recipe1), $this->isInstanceOf(EntityData::class)],
                                [$this->identicalTo($recipe2), $this->isInstanceOf(EntityData::class)],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $result1,
                                $result2,
                            );

        $instance = $this->createInstance();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
