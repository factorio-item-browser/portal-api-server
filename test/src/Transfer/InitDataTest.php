<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\InitData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the InitData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Transfer\InitData
 */
class InitDataTest extends TestCase
{
    public function testConstruct(): void
    {
        $instance = new InitData();

        $this->assertEquals(new SettingMetaData(), $instance->setting);
    }
}
