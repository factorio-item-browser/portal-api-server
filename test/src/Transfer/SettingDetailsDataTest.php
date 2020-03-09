<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\ModData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the SettingDetailsData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData
 */
class SettingDetailsDataTest extends TestCase
{
    /**
     * Tests the setting and getting the mods.
     * @covers ::getMods
     * @covers ::setMods
     */
    public function testSetAndGetMods(): void
    {
        $mods = [
            $this->createMock(ModData::class),
            $this->createMock(ModData::class),
        ];
        $transfer = new SettingDetailsData();

        $this->assertSame($transfer, $transfer->setMods($mods));
        $this->assertSame($mods, $transfer->getMods());
    }
}
