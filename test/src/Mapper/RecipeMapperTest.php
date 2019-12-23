<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\Item;
use FactorioItemBrowser\Api\Client\Entity\Recipe;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Common\Constant\RecipeMode;
use FactorioItemBrowser\PortalApi\Server\Mapper\RecipeMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeItemData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use stdClass;

/**
 * The PHPUnit test of the RecipeMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Mapper\RecipeMapper
 */
class RecipeMapperTest extends TestCase
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
        $mapper = new RecipeMapper();
        $mapper->setMapperManager($this->mapperManager);

        $this->assertSame($this->mapperManager, $this->extractProperty($mapper, 'mapperManager'));
    }

    /**
     * Provides the data for the supports test.
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
     * Tests the supports method.
     * @param object $source
     * @param object $destination
     * @param bool $expectedResult
     * @covers ::supports
     * @dataProvider provideSupports
     */
    public function testSupports(object $source, object $destination, bool $expectedResult): void
    {
        $mapper = new RecipeMapper();
        $mapper->setMapperManager($this->mapperManager);

        $result = $mapper->supports($source, $destination);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the map test.
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
     * Tests the map method.
     * @param string $recipeMode
     * @param bool $expectedIsExpensive
     * @covers ::map
     * @dataProvider provideMap
     */
    public function testMap(string $recipeMode, bool $expectedIsExpensive): void
    {
        /* @var Item&MockObject $ingredient1 */
        $ingredient1 = $this->createMock(Item::class);
        /* @var Item&MockObject $ingredient2 */
        $ingredient2 = $this->createMock(Item::class);
        /* @var Item&MockObject $product1 */
        $product1 = $this->createMock(Item::class);
        /* @var Item&MockObject $product2 */
        $product2 = $this->createMock(Item::class);

        /* @var RecipeItemData&MockObject $ingredientData1 */
        $ingredientData1 = $this->createMock(RecipeItemData::class);
        /* @var RecipeItemData&MockObject $ingredientData2 */
        $ingredientData2 = $this->createMock(RecipeItemData::class);
        /* @var RecipeItemData&MockObject $productData1 */
        $productData1 = $this->createMock(RecipeItemData::class);
        /* @var RecipeItemData&MockObject $productData2 */
        $productData2 = $this->createMock(RecipeItemData::class);

        $source = new Recipe();
        $source->setCraftingTime(13.37)
               ->setIngredients([$ingredient1, $ingredient2])
               ->setProducts([$product1, $product2])
               ->setMode($recipeMode);

        $expectedDestination = new RecipeData();
        $expectedDestination->setCraftingTime(13.37)
                            ->setIngredients([$ingredientData1, $ingredientData2])
                            ->setProducts([$productData1, $productData2])
                            ->setIsExpensive($expectedIsExpensive);

        $destination = new RecipeData();

        /* @var RecipeMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(RecipeMapper::class)
                       ->onlyMethods(['mapItem'])
                       ->getMock();
        $mapper->expects($this->exactly(4))
               ->method('mapItem')
               ->withConsecutive(
                   [$this->identicalTo($ingredient1)],
                   [$this->identicalTo($ingredient2)],
                   [$this->identicalTo($product1)],
                   [$this->identicalTo($product2)]
               )
               ->willReturnOnConsecutiveCalls(
                   $ingredientData1,
                   $ingredientData2,
                   $productData1,
                   $productData2
               );
        $mapper->setMapperManager($this->mapperManager);

        $mapper->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }

    /**
     * Tests the mapItem method.
     * @throws ReflectionException
     * @covers ::mapItem
     */
    public function testMapItem(): void
    {
        /* @var Item&MockObject $item */
        $item = $this->createMock(Item::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($item), $this->isInstanceOf(RecipeItemData::class));

        $mapper = new RecipeMapper();
        $mapper->setMapperManager($this->mapperManager);

        $this->invokeMethod($mapper, 'mapItem', $item);
    }
}
