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
 * @implements StaticMapperInterface<SidebarEntityData, SidebarEntity>
 */
class SidebarEntityDataMapper implements StaticMapperInterface
{
    public function getSupportedSourceClass(): string
    {
        return SidebarEntityData::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return SidebarEntity::class;
    }

    /**
     * @param SidebarEntityData $source
     * @param SidebarEntity $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->setType($source->type)
                    ->setName($source->name)
                    ->setLabel($source->label)
                    ->setPinnedPosition($source->pinnedPosition)
                    ->setLastViewTime($source->lastViewTime);
    }
}
