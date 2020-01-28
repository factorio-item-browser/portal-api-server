<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;

/**
 * The mapper of the sidebar entity data, mapping to database entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SidebarEntityDataMapper implements StaticMapperInterface
{
    /**
     * Returns the source class supported by this mapper.
     * @return string
     */
    public function getSupportedSourceClass(): string
    {
        return SidebarEntityData::class;
    }

    /**
     * Returns the destination class supported by this mapper.
     * @return string
     */
    public function getSupportedDestinationClass(): string
    {
        return SidebarEntity::class;
    }

    /**
     * Maps the source object to the destination one.
     * @param SidebarEntityData $source
     * @param SidebarEntity $destination
     */
    public function map($source, $destination): void
    {
        $destination->setType($source->getType())
                    ->setName($source->getName())
                    ->setLabel($source->getLabel())
                    ->setPinnedPosition($source->getPinnedPosition())
                    ->setLastViewTime($source->getLastViewTime());
    }
}
