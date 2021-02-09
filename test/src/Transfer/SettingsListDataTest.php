<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingsListData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the SettingsListData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Transfer\SettingsListData
 */
class SettingsListDataTest extends TestCase
{
    public function testConstruct(): void
    {
        $instance = new SettingsListData();

        $this->assertEquals(new SettingDetailsData(), $instance->currentSetting);
    }
}
