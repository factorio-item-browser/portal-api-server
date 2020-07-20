<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\InitData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the InitData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\InitData
 */
class InitDataTest extends TestCase
{
    /**
     * Tests the setting and getting the user id.
     * @covers ::getUserId
     * @covers ::setUserId
     */
    public function testSetAndGetUserId(): void
    {
        $userId = 'abc';
        $transfer = new InitData();

        $this->assertSame($transfer, $transfer->setUserId($userId));
        $this->assertSame($userId, $transfer->getUserId());
    }

    /**
     * Tests the setting and getting the setting.
     * @covers ::getSetting
     * @covers ::setSetting
     */
    public function testSetAndGetSetting(): void
    {
        /* @var SettingMetaData&MockObject $setting */
        $setting = $this->createMock(SettingMetaData::class);
        $transfer = new InitData();

        $this->assertSame($transfer, $transfer->setSetting($setting));
        $this->assertSame($setting, $transfer->getSetting());
    }

    /**
     * Tests the setting and getting the setting hash.
     * @covers ::getSettingHash
     * @covers ::setSettingHash
     */
    public function testSetAndGetSettingHash(): void
    {
        $settingHash = 'abc';
        $transfer = new InitData();

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
        $transfer = new InitData();

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
        $transfer = new InitData();

        $this->assertSame($transfer, $transfer->setSidebarEntities($sidebarEntities));
        $this->assertSame($sidebarEntities, $transfer->getSidebarEntities());
    }

    /**
     * Tests the setting and getting the script version.
     * @covers ::getScriptVersion
     * @covers ::setScriptVersion
     */
    public function testSetAndGetScriptVersion(): void
    {
        $scriptVersion = 'abc';
        $transfer = new InitData();

        $this->assertSame($transfer, $transfer->setScriptVersion($scriptVersion));
        $this->assertSame($scriptVersion, $transfer->getScriptVersion());
    }
}
