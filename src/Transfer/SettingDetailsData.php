<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The class representing the detailed data of a setting.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingDetailsData extends SettingMetaData
{
    /**
     * The mods of the setting.
     * @var array|ModData[]
     */
    protected $mods = [];

    /**
     * Sets the mods of the setting.
     * @param array|ModData[] $mods
     * @return $this
     */
    public function setMods(array $mods): self
    {
        $this->mods = $mods;
        return $this;
    }

    /**
     * Returns the mods of the setting.
     * @return array|ModData[]
     */
    public function getMods(): array
    {
        return $this->mods;
    }
}
