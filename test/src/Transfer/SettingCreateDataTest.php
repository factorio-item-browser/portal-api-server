<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\SettingCreateData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the SettingCreateData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\SettingCreateData
 */
class SettingCreateDataTest extends TestCase
{
    /**
     * Tests the setting and getting the mod names.
     * @covers ::getModNames
     * @covers ::setModNames
     */
    public function testSetAndGetModNames(): void
    {
        $modNames = ['abc', 'def'];
        $transfer = new SettingCreateData();

        $this->assertSame($transfer, $transfer->setModNames($modNames));
        $this->assertSame($modNames, $transfer->getModNames());
    }
}
