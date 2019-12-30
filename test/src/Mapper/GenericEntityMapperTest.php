<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Entity\Recipe;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\PortalApi\Server\Helper\RecipeSelector;
use FactorioItemBrowser\PortalApi\Server\Mapper\GenericEntityMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use stdClass;

/**
 * The PHPUnit test of the GenericEntityMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Mapper\GenericEntityMapper
 */
class GenericEntityMapperTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked mapper manager.
     * @var MapperManagerInterface&MockObject
     */
    protected $mapperManager;

    /**
     * The mocked recipe selector.
     * @var RecipeSelector&MockObject
     */
    protected $recipeSelector;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->recipeSelector = $this->createMock(RecipeSelector::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     * @covers ::setMapperManager
     */
    public function testConstructAndSetMapperManager(): void
    {
        $mapper = new GenericEntityMapper($this->recipeSelector);
        $mapper->setMapperManager($this->mapperManager);

        $this->assertSame($this->recipeSelector, $this->extractProperty($mapper, 'recipeSelector'));
        $this->assertSame($this->mapperManager, $this->extractProperty($mapper, 'mapperManager'));
    }

    /**
     * Provides the data for the supports test.
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
     * Tests the supports method.
     * @param object $source
     * @param object $destination
     * @param bool $expectedResult
     * @covers ::supports
     * @dataProvider provideSupports
     */
    public function testSupports(object $source, object $destination, bool $expectedResult): void
    {
        $mapper = new GenericEntityMapper($this->recipeSelector);
        $mapper->setMapperManager($this->mapperManager);

        $result = $mapper->supports($source, $destination);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the map method.
     * @covers ::map
     */
    public function testMap(): void
    {
        $source = new GenericEntity();
        $source->setType('abc')
               ->setName('def')
               ->setLabel('ghi');

        $expectedDestination = new EntityData();
        $expectedDestination->setType('abc')
                            ->setName('def')
                            ->setLabel('ghi');

        $destination = new EntityData();

        $this->recipeSelector->expects($this->never())
                             ->method('selectArray');

        /* @var GenericEntityMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(GenericEntityMapper::class)
                       ->onlyMethods(['mapRecipe'])
                       ->setConstructorArgs([$this->recipeSelector])
                       ->getMock();
        $mapper->expects($this->never())
               ->method('mapRecipe');
        $mapper->setMapperManager($this->mapperManager);

        $mapper->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }

    /**
     * Tests the map method.
     * @covers ::map
     */
    public function testMapWithRecipes(): void
    {
        $sourceRecipes = [
            $this->createMock(RecipeWithExpensiveVersion::class),
            $this->createMock(RecipeWithExpensiveVersion::class),
        ];
        /* @var Recipe&MockObject $selectedRecipe1 */
        $selectedRecipe1 = $this->createMock(Recipe::class);
        /* @var Recipe&MockObject $selectedRecipe2 */
        $selectedRecipe2 = $this->createMock(Recipe::class);
        /* @var RecipeData&MockObject $recipeData1 */
        $recipeData1 = $this->createMock(RecipeData::class);
        /* @var RecipeData&MockObject $recipeData2 */
        $recipeData2 = $this->createMock(RecipeData::class);

        $selectedRecipes = [$selectedRecipe1, $selectedRecipe2];

        $source = new GenericEntityWithRecipes();
        $source->setType('abc')
               ->setName('def')
               ->setLabel('ghi')
               ->setRecipes($sourceRecipes)
               ->setTotalNumberOfRecipes(42);

        $expectedDestination = new EntityData();
        $expectedDestination->setType('abc')
                            ->setName('def')
                            ->setLabel('ghi')
                            ->setRecipes([$recipeData1, $recipeData2])
                            ->setNumberOfRecipes(42);

        $destination = new EntityData();

        $this->recipeSelector->expects($this->once())
                             ->method('selectArray')
                             ->with($this->identicalTo($sourceRecipes))
                             ->willReturn($selectedRecipes);

        /* @var GenericEntityMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(GenericEntityMapper::class)
                       ->onlyMethods(['mapRecipe'])
                       ->setConstructorArgs([$this->recipeSelector])
                       ->getMock();
        $mapper->expects($this->exactly(2))
               ->method('mapRecipe')
               ->withConsecutive(
                   [$this->identicalTo($selectedRecipe1)],
                   [$this->identicalTo($selectedRecipe2)]
               )
               ->willReturnOnConsecutiveCalls(
                   $recipeData1,
                   $recipeData2
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
                            ->with($this->identicalTo($recipe), $this->isInstanceOf(RecipeData::class));

        $mapper = new GenericEntityMapper($this->recipeSelector);
        $mapper->setMapperManager($this->mapperManager);

        $this->invokeMethod($mapper, 'mapRecipe', $recipe);
    }
}
