<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\Recipe;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\PortalApi\Server\Helper\RecipeSelector;
use FactorioItemBrowser\PortalApi\Server\Mapper\RecipeDetailsMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeDetailsData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the RecipeDetailsMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Mapper\RecipeDetailsMapper
 */
class RecipeDetailsMapperTest extends TestCase
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
        $mapper = new RecipeDetailsMapper($this->recipeSelector);
        $mapper->setMapperManager($this->mapperManager);

        $this->assertSame($this->recipeSelector, $this->extractProperty($mapper, 'recipeSelector'));
        $this->assertSame($this->mapperManager, $this->extractProperty($mapper, 'mapperManager'));
    }

    /**
     * Tests the getSupportedSourceClass method.
     * @covers ::getSupportedSourceClass
     */
    public function testGetSupportedSourceClass(): void
    {
        $expectedResult = RecipeWithExpensiveVersion::class;

        $mapper = new RecipeDetailsMapper($this->recipeSelector);
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
        $expectedResult = RecipeDetailsData::class;

        $mapper = new RecipeDetailsMapper($this->recipeSelector);
        $mapper->setMapperManager($this->mapperManager);

        $result = $mapper->getSupportedDestinationClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the map method.
     * @throws MapperException
     * @covers ::map
     */
    public function testMap(): void
    {
        $source = new RecipeWithExpensiveVersion();
        $source->setName('abc')
               ->setLabel('def')
               ->setDescription('ghi');

        /* @var Recipe&MockObject $recipe */
        $recipe = $this->createMock(Recipe::class);
        /* @var Recipe&MockObject $expensiveRecipe */
        $expensiveRecipe = $this->createMock(Recipe::class);
        /* @var RecipeData&MockObject $recipeData */
        $recipeData = $this->createMock(RecipeData::class);
        /* @var RecipeData&MockObject $expensiveRecipeData */
        $expensiveRecipeData = $this->createMock(RecipeData::class);

        $expectedDestination = new RecipeDetailsData();
        $expectedDestination->setName('abc')
                            ->setLabel('def')
                            ->setDescription('ghi')
                            ->setRecipe($recipeData)
                            ->setExpensiveRecipe($expensiveRecipeData);

        $destination = new RecipeDetailsData();

        $this->recipeSelector->expects($this->once())
                             ->method('select')
                             ->with($this->identicalTo($source))
                             ->willReturn([$recipe, $expensiveRecipe]);

        /* @var RecipeDetailsMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(RecipeDetailsMapper::class)
                       ->onlyMethods(['mapRecipe'])
                       ->setConstructorArgs([$this->recipeSelector])
                       ->getMock();
        $mapper->expects($this->exactly(2))
               ->method('mapRecipe')
               ->withConsecutive(
                   [$this->identicalTo($recipe)],
                   [$this->identicalTo($expensiveRecipe)]
               )
               ->willReturnOnConsecutiveCalls(
                   $recipeData,
                   $expensiveRecipeData
               );
        $mapper->setMapperManager($this->mapperManager);

        $mapper->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }

    /**
     * Tests the map method.
     * @throws MapperException
     * @covers ::map
     */
    public function testMapWithSingleRecipe(): void
    {
        $source = new RecipeWithExpensiveVersion();
        $source->setName('abc')
               ->setLabel('def')
               ->setDescription('ghi');

        /* @var Recipe&MockObject $recipe */
        $recipe = $this->createMock(Recipe::class);
        /* @var RecipeData&MockObject $recipeData */
        $recipeData = $this->createMock(RecipeData::class);

        $expectedDestination = new RecipeDetailsData();
        $expectedDestination->setName('abc')
                            ->setLabel('def')
                            ->setDescription('ghi')
                            ->setRecipe($recipeData)
                            ->setExpensiveRecipe(null);

        $destination = new RecipeDetailsData();

        $this->recipeSelector->expects($this->once())
                             ->method('select')
                             ->with($this->identicalTo($source))
                             ->willReturn([$recipe]);

        /* @var RecipeDetailsMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(RecipeDetailsMapper::class)
                       ->onlyMethods(['mapRecipe'])
                       ->setConstructorArgs([$this->recipeSelector])
                       ->getMock();
        $mapper->expects($this->exactly(2))
               ->method('mapRecipe')
               ->withConsecutive(
                   [$this->identicalTo($recipe)],
                   [$this->isNull()]
               )
               ->willReturnOnConsecutiveCalls(
                   $recipeData,
                   null
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

        $mapper = new RecipeDetailsMapper($this->recipeSelector);
        $mapper->setMapperManager($this->mapperManager);

        $result = $this->invokeMethod($mapper, 'mapRecipe', $recipe);

        $this->assertInstanceOf(RecipeData::class, $result);
    }

    /**
     * Tests the mapRecipe method.
     * @throws ReflectionException
     * @covers ::mapRecipe
     */
    public function testMapRecipeWithNull(): void
    {
        $this->mapperManager->expects($this->never())
                            ->method('map');

        $mapper = new RecipeDetailsMapper($this->recipeSelector);
        $mapper->setMapperManager($this->mapperManager);

        $result = $this->invokeMethod($mapper, 'mapRecipe', null);

        $this->assertNull($result);
    }
}
