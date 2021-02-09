<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\Item;
use FactorioItemBrowser\Api\Client\Transfer\Recipe;
use FactorioItemBrowser\Api\Client\Transfer\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Common\Constant\RecipeMode;
use FactorioItemBrowser\PortalApi\Server\Mapper\RecipeMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeItemData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * The PHPUnit test of the RecipeMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Mapper\RecipeMapper
 */
class RecipeMapperTest extends TestCase
{
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;

    protected function setUp(): void
    {
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return RecipeMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): RecipeMapper
    {
        $instance = $this->getMockBuilder(RecipeMapper::class)
                         ->disableProxyingToOriginalMethods()
                         ->onlyMethods($mockedMethods)
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
            [new Recipe(), new RecipeData(), true],
            [new RecipeWithExpensiveVersion(), new RecipeData(), true],
            [new Recipe(), new stdClass(), false],
            [new stdClass(), new RecipeData(), false],
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

    /**
     * @return array<mixed>
     */
    public function provideMap(): array
    {
        return [
            [RecipeMode::NORMAL, false],
            [RecipeMode::EXPENSIVE, true],
        ];
    }

    /**
     * @param string $recipeMode
     * @param bool $expectedIsExpensive
     * @dataProvider provideMap
     */
    public function testMap(string $recipeMode, bool $expectedIsExpensive): void
    {
        $ingredient1 = $this->createMock(Item::class);
        $ingredient2 = $this->createMock(Item::class);
        $product1 = $this->createMock(Item::class);
        $product2 = $this->createMock(Item::class);

        $ingredientData1 = $this->createMock(RecipeItemData::class);
        $ingredientData2 = $this->createMock(RecipeItemData::class);
        $productData1 = $this->createMock(RecipeItemData::class);
        $productData2 = $this->createMock(RecipeItemData::class);

        $source = new Recipe();
        $source->craftingTime = 13.37;
        $source->ingredients = [$ingredient1, $ingredient2];
        $source->products = [$product1, $product2];
        $source->mode = $recipeMode;

        $expectedDestination = new RecipeData();
        $expectedDestination->craftingTime = 13.37;
        $expectedDestination->ingredients = [$ingredientData1, $ingredientData2];
        $expectedDestination->products = [$productData1, $productData2];
        $expectedDestination->isExpensive = $expectedIsExpensive;

        $destination = new RecipeData();

        $this->mapperManager->expects($this->exactly(4))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($ingredient1), $this->isInstanceOf(RecipeItemData::class)],
                                [$this->identicalTo($ingredient2), $this->isInstanceOf(RecipeItemData::class)],
                                [$this->identicalTo($product1), $this->isInstanceOf(RecipeItemData::class)],
                                [$this->identicalTo($product2), $this->isInstanceOf(RecipeItemData::class)],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $ingredientData1,
                                $ingredientData2,
                                $productData1,
                                $productData2,
                            );

        $instance = $this->createInstance();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
