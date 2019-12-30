<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\Recipe;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\PortalApi\Server\Helper\RecipeSelector;
use FactorioItemBrowser\PortalApi\Server\Mapper\RecipeToEntityMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use stdClass;

/**
 * The PHPUnit test of the RecipeToEntityMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Mapper\RecipeToEntityMapper
 */
class RecipeToEntityMapperTest extends TestCase
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
        $mapper = new RecipeToEntityMapper($this->recipeSelector);
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
            [new Recipe(), new EntityData(), true],
            [new RecipeWithExpensiveVersion(), new EntityData(), true],
            [new stdClass(), new EntityData(), false],
            [new Recipe(), new stdClass(), false],
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
        $mapper = new RecipeToEntityMapper($this->recipeSelector);
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
        $source = new Recipe();
        $source->setName('abc')
               ->setLabel('def');

        /* @var Recipe&MockObject $selectedRecipe1 */
        $selectedRecipe1 = $this->createMock(Recipe::class);
        /* @var Recipe&MockObject $selectedRecipe2 */
        $selectedRecipe2 = $this->createMock(Recipe::class);
        /* @var RecipeData&MockObject $recipeData1 */
        $recipeData1 = $this->createMock(RecipeData::class);
        /* @var RecipeData&MockObject $recipeData2 */
        $recipeData2 = $this->createMock(RecipeData::class);

        $expectedDestination = new EntityData();
        $expectedDestination->setType(EntityType::RECIPE)
                            ->setName('abc')
                            ->setLabel('def')
                            ->setRecipes([$recipeData1, $recipeData2])
                            ->setNumberOfRecipes(1);

        $destination = new EntityData();

        $this->recipeSelector->expects($this->once())
                             ->method('select')
                             ->with($this->identicalTo($source))
                             ->willReturn([$selectedRecipe1, $selectedRecipe2]);

        /* @var RecipeToEntityMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(RecipeToEntityMapper::class)
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

        $mapper = new RecipeToEntityMapper($this->recipeSelector);
        $mapper->setMapperManager($this->mapperManager);

        $this->invokeMethod($mapper, 'mapRecipe', $recipe);
    }
}
