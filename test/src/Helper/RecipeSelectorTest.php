<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Helper;

use FactorioItemBrowser\Api\Client\Transfer\Recipe;
use FactorioItemBrowser\Api\Client\Transfer\RecipeWithExpensiveVersion;
use FactorioItemBrowser\PortalApi\Server\Constant\RecipeMode;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Helper\RecipeSelector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeSelector class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Helper\RecipeSelector
 */
class RecipeSelectorTest extends TestCase
{
    /** @var Setting&MockObject */
    private Setting $currentSetting;

    protected function setUp(): void
    {
        $this->currentSetting = $this->createMock(Setting::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return RecipeSelector&MockObject
     */
    private function createInstance(array $mockedMethods = []): RecipeSelector
    {
        return $this->getMockBuilder(RecipeSelector::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->currentSetting,
                    ])
                    ->getMock();
    }

    /**
     * @return array<mixed>
     */
    public function provideSelect(): array
    {
        $normalRecipe1 = new Recipe();
        $normalRecipe1->name = 'abc';
        $normalRecipe1->mode = RecipeMode::NORMAL;

        $normalRecipe2 = new RecipeWithExpensiveVersion();
        $normalRecipe2->name = 'def';
        $normalRecipe2->mode = RecipeMode::NORMAL;

        $expensiveRecipe = new Recipe();
        $expensiveRecipe->name = 'ghi';
        $expensiveRecipe->mode = RecipeMode::EXPENSIVE;

        $normalRecipe3 = new RecipeWithExpensiveVersion();
        $normalRecipe3->name = 'jkl';
        $normalRecipe3->mode = RecipeMode::NORMAL;
        $normalRecipe3->expensiveVersion = $expensiveRecipe;

        $modifiedExpensiveRecipe = new Recipe();
        $modifiedExpensiveRecipe->name = 'ghi';
        $modifiedExpensiveRecipe->mode = RecipeMode::NORMAL;

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
     * @param Recipe $recipe
     * @param string $recipeMode
     * @param array<Recipe> $expectedResult
     * @dataProvider provideSelect
     */
    public function testSelect(Recipe $recipe, string $recipeMode, array $expectedResult): void
    {
        $this->currentSetting->expects($this->once())
                             ->method('getRecipeMode')
                             ->willReturn($recipeMode);

        $instance = $this->createInstance();
        $result = $instance->select($recipe);

        $this->assertEquals($expectedResult, $result);
    }

    public function testSelectArray(): void
    {
        $recipe1 = $this->createMock(Recipe::class);
        $recipe2 = $this->createMock(Recipe::class);
        $selectedRecipe1a = $this->createMock(Recipe::class);
        $selectedRecipe1b = $this->createMock(Recipe::class);
        $selectedRecipe2a = $this->createMock(Recipe::class);
        $selectedRecipe2b = $this->createMock(Recipe::class);

        $recipes = [$recipe1, $recipe2];
        $expectedResult = [$selectedRecipe1a, $selectedRecipe1b, $selectedRecipe2a, $selectedRecipe2b];

        $instance = $this->createInstance(['select']);
        $instance->expects($this->exactly(2))
                 ->method('select')
                 ->withConsecutive(
                     [$this->identicalTo($recipe1)],
                     [$this->identicalTo($recipe2)]
                 )
                 ->willReturnOnConsecutiveCalls(
                     [$selectedRecipe1a, $selectedRecipe1b],
                     [$selectedRecipe2a, $selectedRecipe2b]
                 );
        $result = $instance->selectArray($recipes);

        $this->assertSame($expectedResult, $result);
    }
}
