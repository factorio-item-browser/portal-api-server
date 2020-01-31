<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The object representing the data for initializing the session.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SessionInitData
{
    /**
     * The sidebar entities of the session.
     * @var array<SidebarEntityData>
     */
    protected $sidebarEntities = [];

    /**
     * Sets the sidebar entities of the session.
     * @param array<SidebarEntityData> $sidebarEntities
     * @return $this
     */
    public function setSidebarEntities(array $sidebarEntities): self
    {
        $this->sidebarEntities = $sidebarEntities;
        return $this;
    }

    /**
     * Returns the sidebar entities of the session.
     * @return array<SidebarEntityData>
     */
    public function getSidebarEntities(): array
    {
        return $this->sidebarEntities;
    }
}
