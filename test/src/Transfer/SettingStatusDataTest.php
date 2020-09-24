<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use DateTime;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingStatusData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the SettingStatusData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\SettingStatusData
 */
class SettingStatusDataTest extends TestCase
{
    /**
     * Tests the setting and getting the status.
     * @covers ::getStatus
     * @covers ::setStatus
     */
    public function testSetAndGetStatus(): void
    {
        $status = 'abc';
        $transfer = new SettingStatusData();

        $this->assertSame($transfer, $transfer->setStatus($status));
        $this->assertSame($status, $transfer->getStatus());
    }

    /**
     * Tests the setting and getting the export time.
     * @covers ::getExportTime
     * @covers ::setExportTime
     */
    public function testSetAndGetExportTime(): void
    {
        /* @var DateTime&MockObject $exportTime */
        $exportTime = $this->createMock(DateTime::class);
        $transfer = new SettingStatusData();

        $this->assertSame($transfer, $transfer->setExportTime($exportTime));
        $this->assertSame($exportTime, $transfer->getExportTime());
    }

    /**
     * Tests the setting and getting the existing setting.
     * @covers ::getExistingSetting
     * @covers ::setExistingSetting
     */
    public function testSetAndGetExistingSetting(): void
    {
        $existingSetting = $this->createMock(SettingMetaData::class);
        $transfer = new SettingStatusData();

        $this->assertSame($transfer, $transfer->setExistingSetting($existingSetting));
        $this->assertSame($existingSetting, $transfer->getExistingSetting());
    }
}
