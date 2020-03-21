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
     * The locale of the setting.
     * @var string
     */
    protected $locale = '';

    /**
     * The recipe mode of the setting.
     * @var string
     */
    protected $recipeMode = '';

    /**
     * The mods of the setting.
     * @var array|ModData[]
     */
    protected $mods = [];

    /**
     * The additional style required for the mod icons.
     * @var IconsStyleData
     */
    protected $modIconsStyle;

    /**
     * Initializes the transfer object.
     */
    public function __construct()
    {
        $this->modIconsStyle = new IconsStyleData();
    }

    /**
     * Sets the locale of the setting.
     * @param string $locale
     * @return $this
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Returns the locale of the setting.
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Sets the recipe mode of the setting.
     * @param string $recipeMode
     * @return $this
     */
    public function setRecipeMode(string $recipeMode): self
    {
        $this->recipeMode = $recipeMode;
        return $this;
    }

    /**
     * Returns the recipe mode of the setting.
     * @return string
     */
    public function getRecipeMode(): string
    {
        return $this->recipeMode;
    }

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

    /**
     * Sets the additional style required for the mod icons.
     * @param IconsStyleData $modIconsStyle
     * @return $this
     */
    public function setModIconsStyle(IconsStyleData $modIconsStyle): self
    {
        $this->modIconsStyle = $modIconsStyle;
        return $this;
    }

    /**
     * Returns the additional style required for the mod icons.
     * @return IconsStyleData
     */
    public function getModIconsStyle(): IconsStyleData
    {
        return $this->modIconsStyle;
    }
}
