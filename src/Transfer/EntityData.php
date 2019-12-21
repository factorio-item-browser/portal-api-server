<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The object representing the data of an entity including its recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class EntityData
{
    /**
     * The type of the entity.
     * @var string
     */
    protected $type = '';

    /**
     * The internal name of the entity.
     * @var string
     */
    protected $name = '';

    /**
     * The translated label of the entity.
     * @var string
     */
    protected $label = '';

    /**
     * The recipes of the entity.
     * @var array<RecipeData>
     */
    protected $recipes = [];

    /**
     * The total number of recipes available for the entity.
     * @var int
     */
    protected $numberOfRecipes = 0;

    /**
     * Sets the type of the entity.
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Returns the type of the entity.
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets the internal name of the entity.
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the internal name of the entity.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the translated label of the entity.
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Returns the translated label of the entity.
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Sets the recipes of the entity.
     * @param array<RecipeData> $recipes
     * @return $this
     */
    public function setRecipes(array $recipes): self
    {
        $this->recipes = $recipes;
        return $this;
    }

    /**
     * Returns the recipes of the entity.
     * @return array<RecipeData>
     */
    public function getRecipes(): array
    {
        return $this->recipes;
    }

    /**
     * Sets the total number of recipes available for the entity.
     * @param int $numberOfRecipes
     * @return $this
     */
    public function setNumberOfRecipes(int $numberOfRecipes): self
    {
        $this->numberOfRecipes = $numberOfRecipes;
        return $this;
    }

    /**
     * Returns the total number of recipes available for the entity.
     * @return int
     */
    public function getNumberOfRecipes(): int
    {
        return $this->numberOfRecipes;
    }
}
