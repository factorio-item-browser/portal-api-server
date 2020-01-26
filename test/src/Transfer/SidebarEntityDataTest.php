<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use DateTimeImmutable;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the SidebarEntityData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData
 */
class SidebarEntityDataTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $transfer = new SidebarEntityData();

        $this->assertInstanceOf(DateTimeImmutable::class, $transfer->getLastViewTime());
    }

    /**
     * Tests the setting and getting the type.
     * @covers ::getType
     * @covers ::setType
     */
    public function testSetAndGetType(): void
    {
        $type = 'abc';
        $transfer = new SidebarEntityData();

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
        $transfer = new SidebarEntityData();

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
        $transfer = new SidebarEntityData();

        $this->assertSame($transfer, $transfer->setLabel($label));
        $this->assertSame($label, $transfer->getLabel());
    }

    /**
     * Tests the setting and getting the pinned position.
     * @covers ::getPinnedPosition
     * @covers ::setPinnedPosition
     */
    public function testSetAndGetPinnedPosition(): void
    {
        $pinnedPosition = 42;
        $transfer = new SidebarEntityData();

        $this->assertSame($transfer, $transfer->setPinnedPosition($pinnedPosition));
        $this->assertSame($pinnedPosition, $transfer->getPinnedPosition());
    }

    /**
     * Tests the setting and getting the last view time.
     * @covers ::getLastViewTime
     * @covers ::setLastViewTime
     */
    public function testSetAndGetLastViewTime(): void
    {
        $lastViewTime = new DateTimeImmutable('2038-01-19 03:14:07');
        $transfer = new SidebarEntityData();

        $this->assertSame($transfer, $transfer->setLastViewTime($lastViewTime));
        $this->assertSame($lastViewTime, $transfer->getLastViewTime());
    }
}
