<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the SettingDetailsData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData
 */
class SettingDetailsDataTest extends TestCase
{
    public function testConstruct(): void
    {
        $instance = new SettingDetailsData();

        $this->assertEquals(new IconsStyleData(), $instance->modIconsStyle);
    }
}
