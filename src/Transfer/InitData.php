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
    public SettingMetaData $setting;
    public ?SettingMetaData $lastUsedSetting = null;
    public string $locale = '';
    /** @var array<SidebarEntityData> */
    public array $sidebarEntities = [];
    public string $scriptVersion = '';

    public function __construct()
    {
        $this->setting = new SettingMetaData();
    }
}
