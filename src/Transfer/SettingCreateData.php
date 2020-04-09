<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The object representing the data required to create a setting.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingCreateData extends SettingOptionsData
{
    /**
     * The mod names to use in the setting.
     * @var array<string>|string[]
     */
    protected $modNames = [];

    /**
     * Sets the mod names to use in the setting.
     * @param array<string>|string[] $modNames
     * @return $this
     */
    public function setModNames(array $modNames): self
    {
        $this->modNames = $modNames;
        return $this;
    }

    /**
     * Returns the mod names to use in the setting.
     * @return array<string>|string[]
     */
    public function getModNames(): array
    {
        return $this->modNames;
    }
}
