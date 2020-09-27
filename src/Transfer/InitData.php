<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The object representing the data for initializing the session.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class InitData
{
    /**
     * The setting of the session.
     * @var SettingMetaData
     */
    protected $setting;

    /**
     * The last setting used in the session. Only present if the current one is temporary.
     * @var SettingMetaData|null
     */
    protected $lastUsedSetting;

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
     * Sets the last setting used in the session. Only present if the current one is temporary.
     * @param SettingMetaData|null $lastUsedSetting
     * @return $this
     */
    public function setLastUsedSetting(?SettingMetaData $lastUsedSetting): self
    {
        $this->lastUsedSetting = $lastUsedSetting;
        return $this;
    }

    /**
     * Returns the last setting used in the session. Only present if the current one is temporary.
     * @return SettingMetaData|null
     */
    public function getLastUsedSetting(): ?SettingMetaData
    {
        return $this->lastUsedSetting;
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
