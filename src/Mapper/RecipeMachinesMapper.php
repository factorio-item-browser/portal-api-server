<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use BluePsyduck\MapperManager\MapperManagerAwareInterface;
use BluePsyduck\MapperManager\MapperManagerAwareTrait;
use FactorioItemBrowser\Api\Client\Transfer\Machine;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeMachinesResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\MachineData;
use FactorioItemBrowser\PortalApi\Server\Transfer\RecipeMachinesData;

/**
 * The mapper of the recipe machines response.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<RecipeMachinesResponse, RecipeMachinesData>
 */
class RecipeMachinesMapper implements StaticMapperInterface, MapperManagerAwareInterface
{
    use MapperManagerAwareTrait;

    public function getSupportedSourceClass(): string
    {
        return RecipeMachinesResponse::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return RecipeMachinesData::class;
    }

    /**
     * @param RecipeMachinesResponse $source
     * @param RecipeMachinesData $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->results = array_map([$this, 'mapMachine'], $source->machines);
        $destination->numberOfResults = $source->totalNumberOfResults;
    }

    private function mapMachine(Machine $machine): MachineData
    {
        return $this->mapperManager->map($machine, new MachineData());
    }
}
