<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\DynamicMapperInterface;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;

/**
 * The mapper of the sidebar entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SidebarEntityMapper implements DynamicMapperInterface
{
    /**
     * Returns whether the mapper supports the combination of source and destination object.
     * @param object $source
     * @param object $destination
     * @return bool
     */
    public function supports($source, $destination): bool
    {
        return ($source instanceof SidebarEntity || $source instanceof SidebarEntityData)
            && ($destination instanceof SidebarEntity || $destination instanceof SidebarEntityData);
    }

    /**
     * Maps the source object to the destination one.
     * @param SidebarEntity|SidebarEntityData $source
     * @param SidebarEntity|SidebarEntityData $destination
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
