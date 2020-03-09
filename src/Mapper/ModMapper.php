<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\Api\Client\Entity\Mod;
use FactorioItemBrowser\PortalApi\Server\Transfer\ModData;

/**
 * The mapper of the mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModMapper implements StaticMapperInterface
{
    /**
     * Returns the source class supported by this mapper.
     * @return string
     */
    public function getSupportedSourceClass(): string
    {
        return Mod::class;
    }

    /**
     * Returns the destination class supported by this mapper.
     * @return string
     */
    public function getSupportedDestinationClass(): string
    {
        return ModData::class;
    }

    /**
     * Maps the source object to the destination one.
     * @param Mod $source
     * @param ModData $destination
     */
    public function map($source, $destination): void
    {
        $destination->setName($source->getName())
                    ->setLabel($source->getLabel())
                    ->setAuthor($source->getAuthor())
                    ->setVersion($source->getVersion());
    }
}
