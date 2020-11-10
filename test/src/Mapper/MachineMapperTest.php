<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use FactorioItemBrowser\Api\Client\Entity\Machine;
use FactorioItemBrowser\PortalApi\Server\Mapper\MachineMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\MachineData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the MachineMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Mapper\MachineMapper
 */
class MachineMapperTest extends TestCase
{
    /**
     * Tests the getSupportedSourceClass method.
     * @covers ::getSupportedSourceClass
     */
    public function testGetSupportedSourceClass(): void
    {
        $expectedResult = Machine::class;

        $mapper = new MachineMapper();
        $result = $mapper->getSupportedSourceClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getSupportedDestinationClass method.
     * @covers ::getSupportedDestinationClass
     */
    public function testGetSupportedDestinationClass(): void
    {
        $expectedResult = MachineData::class;

        $mapper = new MachineMapper();
        $result = $mapper->getSupportedDestinationClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the map method.
     * @covers ::map
     */
    public function testMap(): void
    {
        $source = new Machine();
        $source->setName('abc')
               ->setLabel('def')
               ->setCraftingSpeed(13.37)
               ->setNumberOfItemSlots(12)
               ->setNumberOfFluidInputSlots(34)
               ->setNumberOfModuleSlots(56)
               ->setEnergyUsage(4.2)
               ->setEnergyUsageUnit('ghi');

        $expectedDestination = new MachineData();
        $expectedDestination->setName('abc')
                            ->setLabel('def')
                            ->setCraftingSpeed(13.37)
                            ->setNumberOfItems(12)
                            ->setNumberOfFluids(34)
                            ->setNumberOfModules(56)
                            ->setEnergyUsage(4.2)
                            ->setEnergyUsageUnit('ghi');

        $destination = new MachineData();

        $mapper = new MachineMapper();
        $mapper->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
