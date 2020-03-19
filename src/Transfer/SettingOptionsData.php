<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The class representing the options of a setting.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingOptionsData
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
}
