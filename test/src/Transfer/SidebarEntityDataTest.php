<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use DateTime;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the SidebarEntityData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData
 */
class SidebarEntityDataTest extends TestCase
{
    public function testConstruct(): void
    {
        $instance = new SidebarEntityData();

        $this->assertInstanceOf(DateTime::class, $instance->lastViewTime);
    }
}
