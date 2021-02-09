<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Api\Client\Transfer\Machine;
use FactorioItemBrowser\PortalApi\Server\Transfer\MachineData;

/**
 * The mapper of the machines.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<Machine, MachineData>
 */
class MachineMapper implements StaticMapperInterface
{
    public function getSupportedSourceClass(): string
    {
        return Machine::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return MachineData::class;
    }

    /**
     * @param Machine $source
     * @param MachineData $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->name = $source->name;
        $destination->label = $source->label;
        $destination->craftingSpeed = $source->craftingSpeed;
        $destination->numberOfItems = $source->numberOfItemSlots;
        $destination->numberOfFluids = $source->numberOfFluidInputSlots;
        $destination->numberOfModules = $source->numberOfModuleSlots;
        $destination->energyUsage = $source->energyUsage;
        $destination->energyUsageUnit = $source->energyUsageUnit;
    }
}
