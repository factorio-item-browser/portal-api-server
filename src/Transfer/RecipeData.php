<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 *
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeData
{
    /**
     * The crafting time of the recipe, in seconds.
     * @var float
     */
    protected $craftingTime = 0.;

    /**
     * The ingredients of the recipe.
     * @var array
     */
    protected $ingredients = [];

    /**
     * The products of the recipe.
     * @var array
     */
    protected $products = [];

    /**
     * Whether the recipe is from the expensive mode.
     * @var bool
     */
    protected $isExpensive = false;

    /**
     * Sets the crafting time of the recipe, in seconds.
     * @param float $craftingTime
     * @return $this
     */
    public function setCraftingTime(float $craftingTime): self
    {
        $this->craftingTime = $craftingTime;
        return $this;
    }

    /**
     * Returns the crafting time of the recipe, in seconds.
     * @return float
     */
    public function getCraftingTime(): float
    {
        return $this->craftingTime;
    }

    /**
     * Sets the ingredients of the recipe.
     * @param array $ingredients
     * @return $this
     */
    public function setIngredients(array $ingredients): self
    {
        $this->ingredients = $ingredients;
        return $this;
    }

    /**
     * Returns the ingredients of the recipe.
     * @return array
     */
    public function getIngredients(): array
    {
        return $this->ingredients;
    }

    /**
     * Sets the products of the recipe.
     * @param array $products
     * @return $this
     */
    public function setProducts(array $products): self
    {
        $this->products = $products;
        return $this;
    }

    /**
     * Returns the products of the recipe.
     * @return array
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * Sets whether the recipe is from the expensive mode.
     * @param bool $isExpensive
     * @return $this
     */
    public function setIsExpensive(bool $isExpensive): self
    {
        $this->isExpensive = $isExpensive;
        return $this;
    }

    /**
     * Returns whether the recipe is from the expensive mode.
     * @return bool
     */
    public function getIsExpensive(): bool
    {
        return $this->isExpensive;
    }
}
