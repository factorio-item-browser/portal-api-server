<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Transfer\Recipe;
use FactorioItemBrowser\Api\Client\Transfer\RecipeWithExpensiveVersion;
use FactorioItemBrowser\PortalApi\Server\Helper\RecipeSelector;
use FactorioItemBrowser\PortalApi\Server\Mapper\GenericEntityMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * The PHPUnit test of the GenericEntityMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Mapper\GenericEntityMapper
 */
class GenericEntityMapperTest extends TestCase
{
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;
    /** @var RecipeSelector&MockObject */
    private RecipeSelector $recipeSelector;

    protected function setUp(): void
    {
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->recipeSelector = $this->createMock(RecipeSelector::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return GenericEntityMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): GenericEntityMapper
    {
        $instance = $this->getMockBuilder(GenericEntityMapper::class)
                         ->disableProxyingToOriginalMethods()
                         ->onlyMethods($mockedMethods)
                         ->setConstructorArgs([
                             $this->recipeSelector,
                         ])
                         ->getMock();
        $instance->setMapperManager($this->mapperManager);
        return $instance;
    }

    /**
     * @return array<mixed>
     */
    public function provideSupports(): array
    {
        return [
            [new GenericEntity(), new EntityData(), true],
            [new GenericEntityWithRecipes(), new EntityData(), true],
            [new Recipe(), new EntityData(), false],
            [new RecipeWithExpensiveVersion(), new EntityData(), false],
            [new stdClass(), new EntityData(), false],
            [new GenericEntity(), new stdClass(), false],
        ];
    }

    /**
     * @param object $source
     * @param object $destination
     * @param bool $expectedResult
     * @dataProvider provideSupports
     */
    public function testSupports(object $source, object $destination, bool $expectedResult): void
    {
        $instance = $this->createInstance();
        $result = $instance->supports($source, $destination);

        $this->assertSame($expectedResult, $result);
    }

    public function testMap(): void
    {
        $source = new GenericEntity();
        $source->type = 'abc';
        $source->name = 'def';
        $source->label = 'ghi';

        $expectedDestination = new EntityData();
        $expectedDestination->type = 'abc';
        $expectedDestination->name = 'def';
        $expectedDestination->label = 'ghi';

        $destination = new EntityData();

        $this->mapperManager->expects($this->never())
                            ->method('map');
        $this->recipeSelector->expects($this->never())
                             ->method('selectArray');

        $instance = $this->createInstance();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }

    public function testMapWithRecipes(): void
    {
        $sourceRecipes = [
            $this->createMock(RecipeWithExpensiveVersion::class),
            $this->createMock(RecipeWithExpensiveVersion::class),
        ];
        $selectedRecipe1 = $this->createMock(Recipe::class);
        $selectedRecipe2 = $this->createMock(Recipe::class);
        $recipeData1 = $this->createMock(RecipeData::class);
        $recipeData2 = $this->createMock(RecipeData::class);

        $selectedRecipes = [$selectedRecipe1, $selectedRecipe2];

        $source = new GenericEntityWithRecipes();
        $source->type = 'abc';
        $source->name = 'def';
        $source->label = 'ghi';
        $source->recipes = $sourceRecipes;
        $source->totalNumberOfRecipes = 42;

        $expectedDestination = new EntityData();
        $expectedDestination->type = 'abc';
        $expectedDestination->name = 'def';
        $expectedDestination->label = 'ghi';
        $expectedDestination->recipes = [$recipeData1, $recipeData2];
        $expectedDestination->numberOfRecipes = 42;

        $destination = new EntityData();

        $this->recipeSelector->expects($this->once())
                             ->method('selectArray')
                             ->with($this->identicalTo($sourceRecipes))
                             ->willReturn($selectedRecipes);

        $this->mapperManager->expects($this->exactly(2))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($selectedRecipe1), $this->isInstanceOf(RecipeData::class)],
                                [$this->identicalTo($selectedRecipe2), $this->isInstanceOf(RecipeData::class)],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $recipeData1,
                                $recipeData2,
                            );

        $instance = $this->createInstance();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
