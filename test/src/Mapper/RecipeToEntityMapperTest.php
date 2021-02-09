<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\Recipe;
use FactorioItemBrowser\Api\Client\Transfer\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\PortalApi\Server\Helper\RecipeSelector;
use FactorioItemBrowser\PortalApi\Server\Mapper\RecipeToEntityMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * The PHPUnit test of the RecipeToEntityMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Mapper\RecipeToEntityMapper
 */
class RecipeToEntityMapperTest extends TestCase
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
     * @return RecipeToEntityMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): RecipeToEntityMapper
    {
        $instance = $this->getMockBuilder(RecipeToEntityMapper::class)
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
            [new Recipe(), new EntityData(), true],
            [new RecipeWithExpensiveVersion(), new EntityData(), true],
            [new stdClass(), new EntityData(), false],
            [new Recipe(), new stdClass(), false],
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
        $source = new Recipe();
        $source->name = 'abc';
        $source->label = 'def';

        $selectedRecipe1 = $this->createMock(Recipe::class);
        $selectedRecipe2 = $this->createMock(Recipe::class);
        $recipeData1 = $this->createMock(RecipeData::class);
        $recipeData2 = $this->createMock(RecipeData::class);

        $expectedDestination = new EntityData();
        $expectedDestination->type = EntityType::RECIPE;
        $expectedDestination->name = 'abc';
        $expectedDestination->label = 'def';
        $expectedDestination->recipes = [$recipeData1, $recipeData2];
        $expectedDestination->numberOfRecipes = 1;

        $destination = new EntityData();

        $this->recipeSelector->expects($this->once())
                             ->method('select')
                             ->with($this->identicalTo($source))
                             ->willReturn([$selectedRecipe1, $selectedRecipe2]);

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
