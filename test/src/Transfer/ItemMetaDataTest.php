<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\ItemMetaData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ItemMetaData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\ItemMetaData
 */
class ItemMetaDataTest extends TestCase
{
    /**
     * Tests the setting and getting the type.
     * @covers ::getType
     * @covers ::setType
     */
    public function testSetAndGetType(): void
    {
        $type = 'abc';
        $transfer = new ItemMetaData();

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
        $transfer = new ItemMetaData();

        $this->assertSame($transfer, $transfer->setName($name));
        $this->assertSame($name, $transfer->getName());
    }
}
