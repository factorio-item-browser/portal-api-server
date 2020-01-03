<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The class representing the list of machines able to craft a recipe.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeMachinesData
{
    /**
     * The machines able to craft the recipe.
     * @var array<MachineData>
     */
    protected $results = [];

    /**
     * The total number of machines.
     * @var int
     */
    protected $numberOfResults = 0;

    /**
     * Sets the machines able to craft the recipe.
     * @param array<MachineData> $results
     * @return $this
     */
    public function setResults(array $results): self
    {
        $this->results = $results;
        return $this;
    }

    /**
     * Returns the machines able to craft the recipe.
     * @return array<MachineData>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Sets the total number of machines.
     * @param int $numberOfResults
     * @return $this
     */
    public function setNumberOfResults(int $numberOfResults): self
    {
        $this->numberOfResults = $numberOfResults;
        return $this;
    }

    /**
     * Returns the total number of machines.
     * @return int
     */
    public function getNumberOfResults(): int
    {
        return $this->numberOfResults;
    }
}
