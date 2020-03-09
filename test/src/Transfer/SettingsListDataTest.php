<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingsListData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the SettingsListData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\SettingsListData
 */
class SettingsListDataTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $transfer = new SettingsListData();

        $this->assertSame([], $transfer->getSettings());

        // Asserted through type-hint
        $transfer->getCurrentSetting();
    }

    /**
     * Tests the setting and getting the settings.
     * @covers ::getSettings
     * @covers ::setSettings
     */
    public function testSetAndGetSettings(): void
    {
        $settings = [
            $this->createMock(SettingMetaData::class),
            $this->createMock(SettingMetaData::class),
        ];
        $transfer = new SettingsListData();

        $this->assertSame($transfer, $transfer->setSettings($settings));
        $this->assertSame($settings, $transfer->getSettings());
    }

    /**
     * Tests the setting and getting the current setting.
     * @covers ::getCurrentSetting
     * @covers ::setCurrentSetting
     */
    public function testSetAndGetCurrentSetting(): void
    {
        /* @var SettingDetailsData&MockObject $currentSetting */
        $currentSetting = $this->createMock(SettingDetailsData::class);
        $transfer = new SettingsListData();

        $this->assertSame($transfer, $transfer->setCurrentSetting($currentSetting));
        $this->assertSame($currentSetting, $transfer->getCurrentSetting());
    }
}
