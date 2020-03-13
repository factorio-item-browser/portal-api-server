<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\SessionInitData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use PHPUnit\Framework\MockObject\MockObject;
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
     * Tests the setting and getting the setting.
     * @covers ::getSetting
     * @covers ::setSetting
     */
    public function testSetAndGetSetting(): void
    {
        /* @var SettingMetaData&MockObject $setting */
        $setting = $this->createMock(SettingMetaData::class);
        $transfer = new SessionInitData();

        $this->assertSame($transfer, $transfer->setSetting($setting));
        $this->assertSame($setting, $transfer->getSetting());
    }

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
