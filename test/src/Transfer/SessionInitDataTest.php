<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\SessionInitData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the SessionInitData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\SessionInitData
 */
class SessionInitDataTest extends TestCase
{
    /**
     * Tests the setting and getting the sidebar entities.
     * @covers ::getSidebarEntities
     * @covers ::setSidebarEntities
     */
    public function testSetAndGetSidebarEntities(): void
    {
        $sidebarEntities = [
            $this->createMock(SidebarEntityData::class),
            $this->createMock(SidebarEntityData::class),
        ];
        $transfer = new SessionInitData();

        $this->assertSame($transfer, $transfer->setSidebarEntities($sidebarEntities));
        $this->assertSame($sidebarEntities, $transfer->getSidebarEntities());
    }
}
