<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\Recipe;
use FactorioItemBrowser\Api\Client\Transfer\RecipeWithExpensiveVersion;
use FactorioItemBrowser\PortalApi\Server\Helper\RecipeSelector;
use FactorioItemBrowser\PortalApi\Server\Mapper\RecipeDetailsMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeDetailsData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeDetailsMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Mapper\RecipeDetailsMapper
 */
class RecipeDetailsMapperTest extends TestCase
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
     * @return RecipeDetailsMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): RecipeDetailsMapper
    {
        $instance = $this->getMockBuilder(RecipeDetailsMapper::class)
                         ->disableProxyingToOriginalMethods()
                         ->onlyMethods($mockedMethods)
                         ->setConstructorArgs([
                             $this->recipeSelector,
                         ])
                         ->getMock();
        $instance->setMapperManager($this->mapperManager);
        return $instance;
    }

    public function testSupports(): void
    {
        $instance = $this->createInstance();

        $this->assertSame(RecipeWithExpensiveVersion::class, $instance->getSupportedSourceClass());
        $this->assertSame(RecipeDetailsData::class, $instance->getSupportedDestinationClass());
    }

    public function testMap(): void
    {
        $source = new RecipeWithExpensiveVersion();
        $source->name = 'abc';
        $source->label = 'def';
        $source->description = 'ghi';

        $recipe = $this->createMock(Recipe::class);
        $expensiveRecipe = $this->createMock(Recipe::class);
        $recipeData = $this->createMock(RecipeData::class);
        $expensiveRecipeData = $this->createMock(RecipeData::class);

        $expectedDestination = new RecipeDetailsData();
        $expectedDestination->name = 'abc';
        $expectedDestination->label = 'def';
        $expectedDestination->description = 'ghi';
        $expectedDestination->recipe = $recipeData;
        $expectedDestination->expensiveRecipe = $expensiveRecipeData;

        $destination = new RecipeDetailsData();

        $this->recipeSelector->expects($this->once())
                             ->method('select')
                             ->with($this->identicalTo($source))
                             ->willReturn([$recipe, $expensiveRecipe]);

        $this->mapperManager->expects($this->exactly(2))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($recipe), $this->isInstanceOf(RecipeData::class)],
                                [$this->identicalTo($expensiveRecipe), $this->isInstanceOf(RecipeData::class)],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $recipeData,
                                $expensiveRecipeData,
                            );

        $instance = $this->createInstance();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }

    public function testMapWithSingleRecipe(): void
    {
        $source = new RecipeWithExpensiveVersion();
        $source->name = 'abc';
        $source->label = 'def';
        $source->description = 'ghi';

        $recipe = $this->createMock(Recipe::class);
        $recipeData = $this->createMock(RecipeData::class);

        $expectedDestination = new RecipeDetailsData();
        $expectedDestination->name = 'abc';
        $expectedDestination->label = 'def';
        $expectedDestination->description = 'ghi';
        $expectedDestination->recipe = $recipeData;

        $destination = new RecipeDetailsData();

        $this->recipeSelector->expects($this->once())
                             ->method('select')
                             ->with($this->identicalTo($source))
                             ->willReturn([$recipe]);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($recipe), $this->isInstanceOf(RecipeData::class))
                            ->willReturn($recipeData);

        $instance = $this->createInstance();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
