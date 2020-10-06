<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The object representing a list of items.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemListData
{
    /**
     * The items of the list.
     * @var array<ItemMetaData>
     */
    protected $results = [];

    /**
     * The total number of items available.
     * @var int
     */
    protected $numberOfResults = 0;

    /**
     * Sets the items of the list.
     * @param array<ItemMetaData> $results
     * @return $this
     */
    public function setResults(array $results): self
    {
        $this->results = $results;
        return $this;
    }

    /**
     * Returns the items of the list.
     * @return array<ItemMetaData>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Sets the total number of items available.
     * @param int $numberOfResults
     * @return $this
     */
    public function setNumberOfResults(int $numberOfResults): self
    {
        $this->numberOfResults = $numberOfResults;
        return $this;
    }

    /**
     * Returns the total number of items available.
     * @return int
     */
    public function getNumberOfResults(): int
    {
        return $this->numberOfResults;
    }
}
