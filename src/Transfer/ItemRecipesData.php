<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The object representing a list of recipes related to an item or fluid.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemRecipesData
{
    /**
     * The type of the requested item or fluid.
     * @var string
     */
    protected $type = '';

    /**
     * The name of the requested item or fluid.
     * @var string
     */
    protected $name = '';

    /**
     * The translated label of the requested item or fluid.
     * @var string
     */
    protected $label = '';

    /**
     * The translated description of the requested item or fluid.
     * @var string
     */
    protected $description = '';

    /**
     * The recipes related to the requested item.
     * @var array<EntityData>
     */
    protected $results = [];

    /**
     * The total number of recipes available for the item.
     * @var int
     */
    protected $numberOfResults = 0;

    /**
     * Sets the type of the requested item or fluid.
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Returns the type of the requested item or fluid.
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets the name of the requested item or fluid.
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the requested item or fluid.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the translated label of the requested item or fluid.
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Returns the translated label of the requested item or fluid.
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Sets the translated description of the requested item or fluid.
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Returns the translated description of the requested item or fluid.
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Sets the recipes related to the requested item.
     * @param array<EntityData> $results
     * @return $this
     */
    public function setResults(array $results): self
    {
        $this->results = $results;
        return $this;
    }

    /**
     * Returns the recipes related to the requested item.
     * @return array<EntityData>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Sets the total number of recipes available for the item.
     * @param int $numberOfResults
     * @return $this
     */
    public function setNumberOfResults(int $numberOfResults): self
    {
        $this->numberOfResults = $numberOfResults;
        return $this;
    }

    /**
     * Returns the total number of recipes available for the item.
     * @return int
     */
    public function getNumberOfResults(): int
    {
        return $this->numberOfResults;
    }
}
