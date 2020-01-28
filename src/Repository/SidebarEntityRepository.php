<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Repository;

use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;

/**
 * The repository of the sidebar entities.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SidebarEntityRepository
{
    /**
     * Creates a new sidebar entity instance.
     * @param Setting $setting
     * @param string $type
     * @param string $name
     * @return SidebarEntity
     */
    public function createSidebarEntity(Setting $setting, string $type, string $name): SidebarEntity
    {
        $sidebarEntity = new SidebarEntity();
        $sidebarEntity->setSetting($setting)
                      ->setType($type)
                      ->setName($name);
        return $sidebarEntity;
    }
}
