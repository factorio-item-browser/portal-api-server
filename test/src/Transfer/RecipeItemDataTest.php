<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeItemData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeItemData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\RecipeItemData
 */
class RecipeItemDataTest extends TestCase
{
    /**
     * Tests the setting and getting the type.
     * @covers ::getType
     * @covers ::setType
     */
    public function testSetAndGetType(): void
    {
        $type = 'abc';
        $transfer = new RecipeItemData();

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
        $transfer = new RecipeItemData();

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
        $transfer = new RecipeItemData();

        $this->assertSame($transfer, $transfer->setLabel($label));
        $this->assertSame($label, $transfer->getLabel());
    }

    /**
     * Tests the setting and getting the amount.
     * @covers ::getAmount
     * @covers ::setAmount
     */
    public function testSetAndGetAmount(): void
    {
        $amount = 13.37;
        $transfer = new RecipeItemData();

        $this->assertSame($transfer, $transfer->setAmount($amount));
        $this->assertSame($amount, $transfer->getAmount());
    }
}
