<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\ModData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ModData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\ModData
 */
class ModDataTest extends TestCase
{
    /**
     * Tests the setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName(): void
    {
        $name = 'abc';
        $transfer = new ModData();

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
        $transfer = new ModData();

        $this->assertSame($transfer, $transfer->setLabel($label));
        $this->assertSame($label, $transfer->getLabel());
    }

    /**
     * Tests the setting and getting the author.
     * @covers ::getAuthor
     * @covers ::setAuthor
     */
    public function testSetAndGetAuthor(): void
    {
        $author = 'abc';
        $transfer = new ModData();

        $this->assertSame($transfer, $transfer->setAuthor($author));
        $this->assertSame($author, $transfer->getAuthor());
    }

    /**
     * Tests the setting and getting the version.
     * @covers ::getVersion
     * @covers ::setVersion
     */
    public function testSetAndGetVersion(): void
    {
        $version = 'abc';
        $transfer = new ModData();

        $this->assertSame($transfer, $transfer->setVersion($version));
        $this->assertSame($version, $transfer->getVersion());
    }
}
