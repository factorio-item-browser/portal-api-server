<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Helper;

use FactorioItemBrowser\Api\Client\Entity\Recipe;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\PortalApi\Server\Constant\RecipeMode;
use FactorioItemBrowser\PortalApi\Server\Helper\RecipeSelector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeSelector class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Helper\RecipeSelector
 */
class RecipeSelectorTest extends TestCase
{
    /**
     * Provides the data for the select test.
     * @return array<mixed>
     */
    public function provideSelect(): array
    {
        $normalRecipe1 = new Recipe();
        $normalRecipe1->setName('abc')
                      ->setMode(RecipeMode::NORMAL);

        $normalRecipe2 = new RecipeWithExpensiveVersion();
        $normalRecipe2->setName('def')
                      ->setMode(RecipeMode::NORMAL);

        $expensiveRecipe = new Recipe();
        $expensiveRecipe->setName('ghi')
                        ->setMode(RecipeMode::EXPENSIVE);

        $normalRecipe3 = new RecipeWithExpensiveVersion();
        $normalRecipe3->setName('jkl')
                      ->setMode(RecipeMode::NORMAL)
                      ->setExpensiveVersion($expensiveRecipe);

        $modifiedExpensiveRecipe = new Recipe();
        $modifiedExpensiveRecipe->setName('ghi')
                                ->setMode(RecipeMode::NORMAL);

        return [
            [$normalRecipe1, RecipeMode::NORMAL, [$normalRecipe1]],
            [$normalRecipe1, RecipeMode::EXPENSIVE, [$normalRecipe1]],
            [$normalRecipe1, RecipeMode::HYBRID, [$normalRecipe1]],

            [$normalRecipe2, RecipeMode::NORMAL, [$normalRecipe2]],
            [$normalRecipe2, RecipeMode::EXPENSIVE, [$normalRecipe2]],
            [$normalRecipe2, RecipeMode::HYBRID, [$normalRecipe2]],

            [$normalRecipe3, RecipeMode::NORMAL, [$normalRecipe3]],
            [$normalRecipe3, RecipeMode::EXPENSIVE, [$modifiedExpensiveRecipe]],
            [$normalRecipe3, RecipeMode::HYBRID, [$normalRecipe3, $expensiveRecipe]],
        ];
    }

    /**
     * Tests the select method.
     * @param Recipe $recipe
     * @param string $preferredMode
     * @param array<Recipe> $expectedResult
     * @covers ::select
     * @dataProvider provideSelect
     */
    public function testSelect(Recipe $recipe, string $preferredMode, array $expectedResult): void
    {
        /* @var RecipeSelector&MockObject $selector */
        $selector = $this->getMockBuilder(RecipeSelector::class)
                         ->onlyMethods(['getPreferredMode'])
                         ->getMock();
        $selector->expects($this->once())
                 ->method('getPreferredMode')
                 ->willReturn($preferredMode);

        $result = $selector->select($recipe);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the selectArray method.
     * @covers ::selectArray
     */
    public function testSelectArray(): void
    {
        /* @var Recipe&MockObject $recipe1 */
        $recipe1 = $this->createMock(Recipe::class);
        /* @var Recipe&MockObject $recipe2 */
        $recipe2 = $this->createMock(Recipe::class);
        /* @var Recipe&MockObject $selectedRecipe1a */
        $selectedRecipe1a = $this->createMock(Recipe::class);
        /* @var Recipe&MockObject $selectedRecipe1b */
        $selectedRecipe1b = $this->createMock(Recipe::class);
        /* @var Recipe&MockObject $selectedRecipe2a */
        $selectedRecipe2a = $this->createMock(Recipe::class);
        /* @var Recipe&MockObject $selectedRecipe2b */
        $selectedRecipe2b = $this->createMock(Recipe::class);

        $recipes = [$recipe1, $recipe2];
        $expectedResult = [$selectedRecipe1a, $selectedRecipe1b, $selectedRecipe2a, $selectedRecipe2b];

        /* @var RecipeSelector&MockObject $selector */
        $selector = $this->getMockBuilder(RecipeSelector::class)
                         ->onlyMethods(['select'])
                         ->getMock();
        $selector->expects($this->exactly(2))
                 ->method('select')
                 ->withConsecutive(
                     [$this->identicalTo($recipe1)],
                     [$this->identicalTo($recipe2)]
                 )
                 ->willReturnOnConsecutiveCalls(
                     [$selectedRecipe1a, $selectedRecipe1b],
                     [$selectedRecipe2a, $selectedRecipe2b]
                 );

        $result = $selector->selectArray($recipes);
        $this->assertSame($expectedResult, $result);
    }
}
