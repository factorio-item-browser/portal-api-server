<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The object representing the details of a recipe.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeDetailsData
{
    /**
     * The name of the recipe.
     * @var string
     */
    protected $name = '';

    /**
     * The translated label of the recipe.
     * @var string
     */
    protected $label = '';

    /**
     * The translated description of the recipe.
     * @var string
     */
    protected $description = '';

    /**
     * The actual recipe for crafting.
     * @var RecipeData|null
     */
    protected $recipe;

    /**
     * The expensive version of the recipe for crafting.
     * @var RecipeData|null
     */
    protected $expensiveRecipe;

    /**
     * Sets the name of the recipe.
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the recipe.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the translated label of the recipe.
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Returns the translated label of the recipe.
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Sets the translated description of the recipe.
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Returns the translated description of the recipe.
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Sets the actual recipe for crafting.
     * @param RecipeData|null $recipe
     * @return $this
     */
    public function setRecipe(?RecipeData $recipe): self
    {
        $this->recipe = $recipe;
        return $this;
    }

    /**
     * Returns the actual recipe for crafting.
     * @return RecipeData|null
     */
    public function getRecipe(): ?RecipeData
    {
        return $this->recipe;
    }

    /**
     * Sets the expensive version of the recipe for crafting.
     * @param RecipeData|null $expensiveRecipe
     * @return $this
     */
    public function setExpensiveRecipe(?RecipeData $expensiveRecipe): self
    {
        $this->expensiveRecipe = $expensiveRecipe;
        return $this;
    }

    /**
     * Returns the expensive version of the recipe for crafting.
     * @return RecipeData|null
     */
    public function getExpensiveRecipe(): ?RecipeData
    {
        return $this->expensiveRecipe;
    }
}
