<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\MachineData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the MachineData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\MachineData
 */
class MachineDataTest extends TestCase
{
    /**
     * Tests the setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName(): void
    {
        $name = 'abc';
        $transfer = new MachineData();

        $this->assertSame($transfer, $transfer->setName($name));
        $this->assertSame($name, $transfer->getName());
    }

    /**
     * Tests the setting and getting the label.
     * @covers ::getLabel
     * @covers ::setLabel
     */
    public function testSetAndGetLabel(): void
    {
        $label = 'abc';
        $transfer = new MachineData();

        $this->assertSame($transfer, $transfer->setLabel($label));
        $this->assertSame($label, $transfer->getLabel());
    }

    /**
     * Tests the setting and getting the crafting speed.
     * @covers ::getCraftingSpeed
     * @covers ::setCraftingSpeed
     */
    public function testSetAndGetCraftingSpeed(): void
    {
        $craftingSpeed = 13.37;
        $transfer = new MachineData();

        $this->assertSame($transfer, $transfer->setCraftingSpeed($craftingSpeed));
        $this->assertSame($craftingSpeed, $transfer->getCraftingSpeed());
    }

    /**
     * Tests the setting and getting the number of items.
     * @covers ::getNumberOfItems
     * @covers ::setNumberOfItems
     */
    public function testSetAndGetNumberOfItems(): void
    {
        $numberOfItems = 42;
        $transfer = new MachineData();

        $this->assertSame($transfer, $transfer->setNumberOfItems($numberOfItems));
        $this->assertSame($numberOfItems, $transfer->getNumberOfItems());
    }

    /**
     * Tests the setting and getting the number of fluids.
     * @covers ::getNumberOfFluids
     * @covers ::setNumberOfFluids
     */
    public function testSetAndGetNumberOfFluids(): void
    {
        $numberOfFluids = 42;
        $transfer = new MachineData();

        $this->assertSame($transfer, $transfer->setNumberOfFluids($numberOfFluids));
        $this->assertSame($numberOfFluids, $transfer->getNumberOfFluids());
    }

    /**
     * Tests the setting and getting the number of modules.
     * @covers ::getNumberOfModules
     * @covers ::setNumberOfModules
     */
    public function testSetAndGetNumberOfModules(): void
    {
        $numberOfModules = 42;
        $transfer = new MachineData();

        $this->assertSame($transfer, $transfer->setNumberOfModules($numberOfModules));
        $this->assertSame($numberOfModules, $transfer->getNumberOfModules());
    }

    /**
     * Tests the setting and getting the energy usage.
     * @covers ::getEnergyUsage
     * @covers ::setEnergyUsage
     */
    public function testSetAndGetEnergyUsage(): void
    {
        $energyUsage = 13.37;
        $transfer = new MachineData();

        $this->assertSame($transfer, $transfer->setEnergyUsage($energyUsage));
        $this->assertSame($energyUsage, $transfer->getEnergyUsage());
    }

    /**
     * Tests the setting and getting the energy usage unit.
     * @covers ::getEnergyUsageUnit
     * @covers ::setEnergyUsageUnit
     */
    public function testSetAndGetEnergyUsageUnit(): void
    {
        $energyUsageUnit = 'abc';
        $transfer = new MachineData();

        $this->assertSame($transfer, $transfer->setEnergyUsageUnit($energyUsageUnit));
        $this->assertSame($energyUsageUnit, $transfer->getEnergyUsageUnit());
    }
}
