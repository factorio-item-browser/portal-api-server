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
     * The name of the currently loaded setting.
     * @var string
     */
    protected $settingName = '';

    /**
     * The locale to use for the page.
     * @var string
     */
    protected $locale = '';

    /**
     * The sidebar entities of the session.
     * @var array<SidebarEntityData>
     */
    protected $sidebarEntities = [];

    /**
     * Sets the name of the currently loaded setting.
     * @param string $settingName
     * @return $this
     */
    public function setSettingName(string $settingName): self
    {
        $this->settingName = $settingName;
        return $this;
    }

    /**
     * Returns the name of the currently loaded setting.
     * @return string
     */
    public function getSettingName(): string
    {
        return $this->settingName;
    }

    /**
     * Sets the locale to use for the page.
     * @param string $locale
     * @return $this
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Returns the locale to use for the page.
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

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
