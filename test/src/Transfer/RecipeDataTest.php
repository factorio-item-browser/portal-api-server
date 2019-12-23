<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeItemData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData
 */
class RecipeDataTest extends TestCase
{
    /**
     * Tests the setting and getting the crafting time.
     * @covers ::getCraftingTime
     * @covers ::setCraftingTime
     */
    public function testSetAndGetCraftingTime(): void
    {
        $craftingTime = 13.37;
        $transfer = new RecipeData();

        $this->assertSame($transfer, $transfer->setCraftingTime($craftingTime));
        $this->assertSame($craftingTime, $transfer->getCraftingTime());
    }

    /**
     * Tests the setting and getting the ingredients.
     * @covers ::getIngredients
     * @covers ::setIngredients
     */
    public function testSetAndGetIngredients(): void
    {
        $ingredients = [
            $this->createMock(RecipeItemData::class),
            $this->createMock(RecipeItemData::class),
        ];
        $transfer = new RecipeData();

        $this->assertSame($transfer, $transfer->setIngredients($ingredients));
        $this->assertSame($ingredients, $transfer->getIngredients());
    }

    /**
     * Tests the setting and getting the products.
     * @covers ::getProducts
     * @covers ::setProducts
     */
    public function testSetAndGetProducts(): void
    {
        $products = [
            $this->createMock(RecipeItemData::class),
            $this->createMock(RecipeItemData::class),
        ];
        $transfer = new RecipeData();

        $this->assertSame($transfer, $transfer->setProducts($products));
        $this->assertSame($products, $transfer->getProducts());
    }

    /**
     * Tests the setting and getting the is expensive.
     * @covers ::getIsExpensive
     * @covers ::setIsExpensive
     */
    public function testSetAndGetIsExpensive(): void
    {
        $transfer = new RecipeData();

        $this->assertSame($transfer, $transfer->setIsExpensive(true));
        $this->assertTrue($transfer->getIsExpensive());
    }
}
