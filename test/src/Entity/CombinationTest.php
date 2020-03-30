<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Entity;

use DateTime;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * The PHPUnit test of the Combination class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Entity\Combination
 */
class CombinationTest extends TestCase
{
    /**
     * Tests the setting and getting the id.
     * @covers ::getId
     * @covers ::setId
     */
    public function testSetAndGetId(): void
    {
        /* @var UuidInterface&MockObject $id */
        $id = $this->createMock(UuidInterface::class);
        $entity = new Combination();

        $this->assertSame($entity, $entity->setId($id));
        $this->assertSame($id, $entity->getId());
    }

    /**
     * Tests the setting and getting the mod names.
     * @covers ::getModNames
     * @covers ::setModNames
     */
    public function testSetAndGetModNames(): void
    {
        $modNames = ['abc', 'def'];
        $entity = new Combination();

        $this->assertSame($entity, $entity->setModNames($modNames));
        $this->assertSame($modNames, $entity->getModNames());
    }

    /**
     * Tests the setting and getting the status.
     * @covers ::getStatus
     * @covers ::setStatus
     */
    public function testSetAndGetStatus(): void
    {
        $status = 'abc';
        $entity = new Combination();

        $this->assertSame($entity, $entity->setStatus($status));
        $this->assertSame($status, $entity->getStatus());
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
        $entity = new Combination();

        $this->assertSame($entity, $entity->setExportTime($exportTime));
        $this->assertSame($exportTime, $entity->getExportTime());
    }

    /**
     * Tests the setting and getting the last check time.
     * @covers ::getLastCheckTime
     * @covers ::setLastCheckTime
     */
    public function testSetAndGetLastCheckTime(): void
    {
        /* @var DateTime&MockObject $lastCheckTime */
        $lastCheckTime = $this->createMock(DateTime::class);
        $entity = new Combination();

        $this->assertSame($entity, $entity->setLastCheckTime($lastCheckTime));
        $this->assertSame($lastCheckTime, $entity->getLastCheckTime());
    }
}
