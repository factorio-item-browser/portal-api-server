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
    public string $locale = '';
    public string $recipeMode = '';
    /** @var array<ModData> */
    public array $mods = [];
    public IconsStyleData $modIconsStyle;

    public function __construct()
    {
        $this->modIconsStyle = new IconsStyleData();
    }
}
