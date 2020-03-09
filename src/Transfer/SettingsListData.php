<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The class representing the data of the settings list.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingsListData
{
    /**
     * The list of available settings.
     * @var array|SettingMetaData[]
     */
    protected $settings = [];

    /**
     * The current setting.
     * @var SettingDetailsData
     */
    protected $currentSetting;

    /**
     * Initializes the transfer object.
     */
    public function __construct()
    {
        $this->currentSetting = new SettingDetailsData();
    }

    /**
     * Sets the list of available settings.
     * @param array|SettingMetaData[] $settings
     * @return $this
     */
    public function setSettings(array $settings): self
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * Returns the list of available settings.
     * @return array|SettingMetaData[]
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * Sets the current setting.
     * @param SettingDetailsData $currentSetting
     * @return $this
     */
    public function setCurrentSetting(SettingDetailsData $currentSetting): self
    {
        $this->currentSetting = $currentSetting;
        return $this;
    }

    /**
     * Returns the current setting.
     * @return SettingDetailsData
     */
    public function getCurrentSetting(): SettingDetailsData
    {
        return $this->currentSetting;
    }
}
