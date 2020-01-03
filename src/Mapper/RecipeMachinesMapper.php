<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use BluePsyduck\MapperManager\MapperManagerAwareInterface;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\Machine;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeMachinesResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\MachineData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeMachinesData;

/**
 * The mapper of the recipe machines response.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeMachinesMapper implements StaticMapperInterface, MapperManagerAwareInterface
{
    /**
     * The mapper manager.
     * @var MapperManagerInterface
     */
    protected $mapperManager;

    /**
     * Sets the mapper manager.
     * @param MapperManagerInterface $mapperManager
     */
    public function setMapperManager(MapperManagerInterface $mapperManager): void
    {
        $this->mapperManager = $mapperManager;
    }

    /**
     * Returns the source class supported by this mapper.
     * @return string
     */
    public function getSupportedSourceClass(): string
    {
        return RecipeMachinesResponse::class;
    }

    /**
     * Returns the destination class supported by this mapper.
     * @return string
     */
    public function getSupportedDestinationClass(): string
    {
        return RecipeMachinesData::class;
    }

        /**
     * Maps the source object to the destination one.
     * @param RecipeMachinesResponse $source
     * @param RecipeMachinesData $destination
     */
    public function map($source, $destination): void
    {
        $destination->setResults(array_map([$this, 'mapMachine'], $source->getMachines()))
                    ->setNumberOfResults($source->getTotalNumberOfResults());
    }

    /**
     * Maps the recipe.
     * @param Machine $machine
     * @return MachineData
     * @throws MapperException
     */
    protected function mapMachine(Machine $machine): MachineData
    {
        $machineData = new MachineData();
        $this->mapperManager->map($machine, $machineData);
        return $machineData;
    }
}
