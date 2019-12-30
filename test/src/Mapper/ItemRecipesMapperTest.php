<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Entity\Recipe;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\PortalApi\Server\Mapper\ItemRecipesMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemRecipesData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ItemRecipesMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Mapper\ItemRecipesMapper
 */
class ItemRecipesMapperTest extends TestCase
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
        $mapper = new ItemRecipesMapper();
        $mapper->setMapperManager($this->mapperManager);

        $this->assertSame($this->mapperManager, $this->extractProperty($mapper, 'mapperManager'));
    }

    /**
     * Tests the getSupportedSourceClass method.
     * @covers ::getSupportedSourceClass
     */
    public function testGetSupportedSourceClass(): void
    {
        $expectedResult = GenericEntityWithRecipes::class;

        $mapper = new ItemRecipesMapper();
        $mapper->setMapperManager($this->mapperManager);

        $result = $mapper->getSupportedSourceClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getSupportedDestinationClass method.
     * @covers ::getSupportedDestinationClass
     */
    public function testGetSupportedDestinationClass(): void
    {
        $expectedResult = ItemRecipesData::class;

        $mapper = new ItemRecipesMapper();
        $mapper->setMapperManager($this->mapperManager);

        $result = $mapper->getSupportedDestinationClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the map method.
     * @covers ::map
     */
    public function testMap(): void
    {
        /* @var RecipeWithExpensiveVersion&MockObject $recipe1 */
        $recipe1 = $this->createMock(RecipeWithExpensiveVersion::class);
        /* @var RecipeWithExpensiveVersion&MockObject $recipe2 */
        $recipe2 = $this->createMock(RecipeWithExpensiveVersion::class);
        /* @var EntityData&MockObject $result1 */
        $result1 = $this->createMock(EntityData::class);
        /* @var EntityData&MockObject $result2 */
        $result2 = $this->createMock(EntityData::class);

        $source = new GenericEntityWithRecipes();
        $source->setType('abc')
               ->setName('def')
               ->setLabel('ghi')
               ->setDescription('jkl')
               ->setRecipes([$recipe1, $recipe2])
               ->setTotalNumberOfRecipes(42);

        $expectedDestination = new ItemRecipesData();
        $expectedDestination->setType('abc')
                            ->setName('def')
                            ->setLabel('ghi')
                            ->setDescription('jkl')
                            ->setResults([$result1, $result2])
                            ->setNumberOfResults(42);

        $destination = new ItemRecipesData();

        /* @var ItemRecipesMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(ItemRecipesMapper::class)
                       ->onlyMethods(['mapRecipe'])
                       ->getMock();
        $mapper->expects($this->exactly(2))
               ->method('mapRecipe')
               ->withConsecutive(
                   [$this->identicalTo($recipe1)],
                   [$this->identicalTo($recipe2)]
               )
               ->willReturnOnConsecutiveCalls(
                   $result1,
                   $result2
               );
        $mapper->setMapperManager($this->mapperManager);

        $mapper->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }

    /**
     * Tests the mapRecipe method.
     * @throws ReflectionException
     * @covers ::mapRecipe
     */
    public function testMapRecipe(): void
    {
        /* @var Recipe&MockObject $recipe */
        $recipe = $this->createMock(Recipe::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($recipe), $this->isInstanceOf(EntityData::class));

        $mapper = new ItemRecipesMapper();
        $mapper->setMapperManager($this->mapperManager);

        $this->invokeMethod($mapper, 'mapRecipe', $recipe);
    }
}
