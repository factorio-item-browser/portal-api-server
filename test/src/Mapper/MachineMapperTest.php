<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use FactorioItemBrowser\Api\Client\Transfer\Machine;
use FactorioItemBrowser\PortalApi\Server\Mapper\MachineMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\MachineData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the MachineMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Mapper\MachineMapper
 */
class MachineMapperTest extends TestCase
{
    /**
     * @param array<string> $mockedMethods
     * @return MachineMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): MachineMapper
    {
        return $this->getMockBuilder(MachineMapper::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->getMock();
    }

    public function testSupports(): void
    {
        $instance = $this->createInstance();

        $this->assertSame(Machine::class, $instance->getSupportedSourceClass());
        $this->assertSame(MachineData::class, $instance->getSupportedDestinationClass());
    }

    public function testMap(): void
    {
        $source = new Machine();
        $source->name = 'abc';
        $source->label = 'def';
        $source->craftingSpeed = 13.37;
        $source->numberOfItemSlots = 12;
        $source->numberOfFluidInputSlots = 34;
        $source->numberOfModuleSlots = 56;
        $source->energyUsage = 4.2;
        $source->energyUsageUnit = 'ghi';

        $expectedDestination = new MachineData();
        $expectedDestination->name = 'abc';
        $expectedDestination->label = 'def';
        $expectedDestination->craftingSpeed = 13.37;
        $expectedDestination->numberOfItems = 12;
        $expectedDestination->numberOfFluids = 34;
        $expectedDestination->numberOfModules = 56;
        $expectedDestination->energyUsage = 4.2;
        $expectedDestination->energyUsageUnit = 'ghi';

        $destination = new MachineData();

        $instance = $this->createInstance();
        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
