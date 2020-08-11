<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the SettingMetaData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData
 */
class SettingMetaDataTest extends TestCase
{
    /**
     * Tests the setting and getting the combination id.
     * @covers ::getCombinationId
     * @covers ::setCombinationId
     */
    public function testSetAndGetCombinationId(): void
    {
        $id = 'abc';
        $transfer = new SettingMetaData();

        $this->assertSame($transfer, $transfer->setCombinationId($id));
        $this->assertSame($id, $transfer->getCombinationId());
    }

    /**
     * Tests the setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName(): void
    {
        $name = 'abc';
        $transfer = new SettingMetaData();

        $this->assertSame($transfer, $transfer->setName($name));
        $this->assertSame($name, $transfer->getName());
    }

    /**
     * Tests the setting and getting the status.
     * @covers ::getStatus
     * @covers ::setStatus
     */
    public function testSetAndGetStatus(): void
    {
        $status = 'abc';
        $transfer = new SettingMetaData();

        $this->assertSame($transfer, $transfer->setStatus($status));
        $this->assertSame($status, $transfer->getStatus());
    }

    /**
     * Tests the setting and getting the is temporary.
     * @covers ::getIsTemporary
     * @covers ::setIsTemporary
     */
    public function testSetAndGetIsTemporary(): void
    {
        $transfer = new SettingMetaData();

        $this->assertSame($transfer, $transfer->setIsTemporary(true));
        $this->assertTrue($transfer->getIsTemporary());
    }
}
