<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeDetailsData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeDetailsData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\RecipeDetailsData
 */
class RecipeDetailsDataTest extends TestCase
{
    /**
     * Tests the setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName(): void
    {
        $name = 'abc';
        $transfer = new RecipeDetailsData();

        $this->assertSame($transfer, $transfer->setName($name));
        $this->assertSame($name, $transfer->getName());
    }

    /**
     * Tests the setting and getting the label.
     * @covers ::getLabel
     * @covers ::setLabel
     */
    public function testSetAndGetLabel(): void
    {
        $label = 'abc';
        $transfer = new RecipeDetailsData();

        $this->assertSame($transfer, $transfer->setLabel($label));
        $this->assertSame($label, $transfer->getLabel());
    }

    /**
     * Tests the setting and getting the description.
     * @covers ::getDescription
     * @covers ::setDescription
     */
    public function testSetAndGetDescription(): void
    {
        $description = 'abc';
        $recipe = new RecipeDetailsData();

        $this->assertSame($recipe, $recipe->setDescription($description));
        $this->assertSame($description, $recipe->getDescription());
    }

    /**
     * Tests the setting and getting the recipe.
     * @covers ::getRecipe
     * @covers ::setRecipe
     */
    public function testSetAndGetRecipe(): void
    {
        /* @var RecipeData&MockObject $recipe */
        $recipe = $this->createMock(RecipeData::class);
        $transfer = new RecipeDetailsData();

        $this->assertSame($transfer, $transfer->setRecipe($recipe));
        $this->assertSame($recipe, $transfer->getRecipe());
    }

    /**
     * Tests the setting and getting the expensive recipe.
     * @covers ::getExpensiveRecipe
     * @covers ::setExpensiveRecipe
     */
    public function testSetAndGetExpensiveRecipe(): void
    {
        /* @var RecipeData&MockObject $expensiveRecipe */
        $expensiveRecipe = $this->createMock(RecipeData::class);
        $transfer = new RecipeDetailsData();

        $this->assertSame($transfer, $transfer->setExpensiveRecipe($expensiveRecipe));
        $this->assertSame($expensiveRecipe, $transfer->getExpensiveRecipe());
    }
}
