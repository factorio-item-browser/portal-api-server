<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\MachineData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeMachinesData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeMachinesData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\RecipeMachinesData
 */
class RecipeMachinesDataTest extends TestCase
{
    /**
     * Tests the setting and getting the results.
     * @covers ::getResults
     * @covers ::setResults
     */
    public function testSetAndGetResults(): void
    {
        $results = [
            $this->createMock(MachineData::class),
            $this->createMock(MachineData::class),
        ];
        $transfer = new RecipeMachinesData();

        $this->assertSame($transfer, $transfer->setResults($results));
        $this->assertSame($results, $transfer->getResults());
    }

    /**
     * Tests the setting and getting the number of results.
     * @covers ::getNumberOfResults
     * @covers ::setNumberOfResults
     */
    public function testSetAndGetNumberOfResults(): void
    {
        $numberOfResults = 42;
        $transfer = new RecipeMachinesData();

        $this->assertSame($transfer, $transfer->setNumberOfResults($numberOfResults));
        $this->assertSame($numberOfResults, $transfer->getNumberOfResults());
    }
}
