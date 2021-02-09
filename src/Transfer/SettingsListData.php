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
    /** @var array<SettingMetaData> */
    public array $settings = [];
    public SettingDetailsData $currentSetting;

    public function __construct()
    {
        $this->currentSetting = new SettingDetailsData();
    }
}
