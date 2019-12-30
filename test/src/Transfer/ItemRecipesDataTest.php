<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\ItemRecipesData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ItemRecipesData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\ItemRecipesData
 */
class ItemRecipesDataTest extends TestCase
{
    /**
     * Tests the setting and getting the type.
     * @covers ::getType
     * @covers ::setType
     */
    public function testSetAndGetType(): void
    {
        $type = 'abc';
        $transfer = new ItemRecipesData();

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
        $transfer = new ItemRecipesData();

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
        $transfer = new ItemRecipesData();

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
        $transfer = new ItemRecipesData();

        $this->assertSame($transfer, $transfer->setDescription($description));
        $this->assertSame($description, $transfer->getDescription());
    }

    /**
     * Tests the setting and getting the results.
     * @covers ::getResults
     * @covers ::setResults
     */
    public function testSetAndGetResults(): void
    {
        $results = [
            $this->createMock(EntityData::class),
            $this->createMock(EntityData::class),
        ];
        $transfer = new ItemRecipesData();

        $this->assertSame($transfer, $transfer->setResults($results));
        $this->assertSame($results, $transfer->getResults());
    }

    /**
     * Tests the setting and getting the number of results.
     * @covers ::getNumberOfResults
     * @covers ::setNumberOfResults
     */
    public function testSetAndGetNumberOfResults(): void
    {
        $numberOfResults = 42;
        $transfer = new ItemRecipesData();

        $this->assertSame($transfer, $transfer->setNumberOfResults($numberOfResults));
        $this->assertSame($numberOfResults, $transfer->getNumberOfResults());
    }
}
