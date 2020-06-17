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
     * The setting of the session.
     * @var SettingMetaData
     */
    protected $setting;

    /**
     * The hash of the currently loaded setting.
     * @var string
     */
    protected $settingHash = '';

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
     * The current version of the scripts.
     * @var string
     */
    protected $scriptVersion = '';

    /**
     * Sets the setting of the session.
     * @param SettingMetaData $setting
     * @return $this
     */
    public function setSetting(SettingMetaData $setting): self
    {
        $this->setting = $setting;
        return $this;
    }

    /**
     * Returns the setting of the session.
     * @return SettingMetaData
     */
    public function getSetting(): SettingMetaData
    {
        return $this->setting;
    }

    /**
     * Sets the hash of the currently loaded setting.
     * @param string $settingHash
     * @return $this
     */
    public function setSettingHash(string $settingHash): self
    {
        $this->settingHash = $settingHash;
        return $this;
    }

    /**
     * Returns the hash of the currently loaded setting.
     * @return string
     */
    public function getSettingHash(): string
    {
        return $this->settingHash;
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

    /**
     * Sets the current version of the scripts.
     * @param string $scriptVersion
     * @return $this
     */
    public function setScriptVersion(string $scriptVersion): self
    {
        $this->scriptVersion = $scriptVersion;
        return $this;
    }

    /**
     * Returns the current version of the scripts.
     * @return string
     */
    public function getScriptVersion(): string
    {
        return $this->scriptVersion;
    }
}
