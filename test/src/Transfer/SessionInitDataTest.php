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
     * Tests the setting and getting the setting name.
     * @covers ::getSettingName
     * @covers ::setSettingName
     */
    public function testSetAndGetSettingName(): void
    {
        $settingName = 'abc';
        $transfer = new SessionInitData();

        $this->assertSame($transfer, $transfer->setSettingName($settingName));
        $this->assertSame($settingName, $transfer->getSettingName());
    }

    /**
     * Tests the setting and getting the setting hash.
     * @covers ::getSettingHash
     * @covers ::setSettingHash
     */
    public function testSetAndGetSettingHash(): void
    {
        $settingHash = 'abc';
        $transfer = new SessionInitData();

        $this->assertSame($transfer, $transfer->setSettingHash($settingHash));
        $this->assertSame($settingHash, $transfer->getSettingHash());
    }

    /**
     * Tests the setting and getting the locale.
     * @covers ::getLocale
     * @covers ::setLocale
     */
    public function testSetAndGetLocale(): void
    {
        $locale = 'abc';
        $transfer = new SessionInitData();

        $this->assertSame($transfer, $transfer->setLocale($locale));
        $this->assertSame($locale, $transfer->getLocale());
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
