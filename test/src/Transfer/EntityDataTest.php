<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the EntityData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\EntityData
 */
class EntityDataTest extends TestCase
{
    /**
     * Tests the setting and getting the type.
     * @covers ::getType
     * @covers ::setType
     */
    public function testSetAndGetType(): void
    {
        $type = 'abc';
        $transfer = new EntityData();

        $this->assertSame($transfer, $transfer->setType($type));
        $this->assertSame($type, $transfer->getType());
    }

    /**
     * Tests the setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName(): void
    {
        $name = 'abc';
        $transfer = new EntityData();

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
        $transfer =  new EntityData();

        $this->assertSame($transfer, $transfer->setLabel($label));
        $this->assertSame($label, $transfer->getLabel());
    }

    /**
     * Tests the setting and getting the recipes.
     * @covers ::getRecipes
     * @covers ::setRecipes
     */
    public function testSetAndGetRecipes(): void
    {
        $recipes = [
            $this->createMock(RecipeData::class),
            $this->createMock(RecipeData::class),
        ];
        $transfer = new EntityData();

        $this->assertSame($transfer, $transfer->setRecipes($recipes));
        $this->assertSame($recipes, $transfer->getRecipes());
    }

    /**
     * Tests the setting and getting the number of recipes.
     * @covers ::getNumberOfRecipes
     * @covers ::setNumberOfRecipes
     */
    public function testSetAndGetNumberOfRecipes(): void
    {
        $numberOfRecipes = 42;
        $transfer = new EntityData();

        $this->assertSame($transfer, $transfer->setNumberOfRecipes($numberOfRecipes));
        $this->assertSame($numberOfRecipes, $transfer->getNumberOfRecipes());
    }
}
