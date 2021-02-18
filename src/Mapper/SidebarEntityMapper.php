<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Transfer\SidebarEntityData;

/**
 * The mapper of the sidebar entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<SidebarEntity, SidebarEntityData>
 */
class SidebarEntityMapper implements StaticMapperInterface
{
    public function getSupportedSourceClass(): string
    {
        return SidebarEntity::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return SidebarEntityData::class;
    }

    /**
     * @param SidebarEntity $source
     * @param SidebarEntityData $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->type = $source->getType();
        $destination->name = $source->getName();
        $destination->label = $source->getLabel();
        $destination->pinnedPosition = $source->getPinnedPosition();
        $destination->lastViewTime = $source->getLastViewTime();
    }
}
