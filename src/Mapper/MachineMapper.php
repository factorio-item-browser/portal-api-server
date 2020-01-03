<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Api\Client\Entity\Machine;
use FactorioItemBrowser\PortalApi\Server\Transfer\MachineData;

/**
 * The mapper of the machines.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineMapper implements StaticMapperInterface
{
    /**
     * Returns the source class supported by this mapper.
     * @return string
     */
    public function getSupportedSourceClass(): string
    {
        return Machine::class;
    }

    /**
     * Returns the destination class supported by this mapper.
     * @return string
     */
    public function getSupportedDestinationClass(): string
    {
        return MachineData::class;
    }

    /**
     * Maps the source object to the destination one.
     * @param Machine $source
     * @param MachineData $destination
     */
    public function map($source, $destination): void
    {
        $destination->setName($source->getName())
                    ->setLabel($source->getLabel())
                    ->setCraftingSpeed($source->getCraftingSpeed())
                    ->setNumberOfItems($source->getNumberOfItemSlots())
                    ->setNumberOfFluids($source->getNumberOfFluidInputSlots())
                    ->setNumberOfModules($source->getNumberOfModuleSlots())
                    ->setEnergyUsage($source->getEnergyUsage())
                    ->setEnergyUsageUnit($source->getEnergyUsageUnit());
    }
}
