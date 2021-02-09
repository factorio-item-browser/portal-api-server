<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Api\Client\Transfer\Mod;
use FactorioItemBrowser\PortalApi\Server\Transfer\ModData;

/**
 * The mapper of the mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<Mod, ModData>
 */
class ModMapper implements StaticMapperInterface
{
    public function getSupportedSourceClass(): string
    {
        return Mod::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return ModData::class;
    }

    /**
     * @param Mod $source
     * @param ModData $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->name = $source->name;
        $destination->label = $source->label;
        $destination->author = $source->author;
        $destination->version = $source->version;
    }
}
