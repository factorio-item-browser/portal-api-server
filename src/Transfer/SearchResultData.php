<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The response containing the search result data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SearchResultData
{
    /**
     * The actual search results.
     * @var array<EntityData>
     */
    protected $results = [];

    /**
     * The total number of results.
     * @var int
     */
    protected $numberOfResults = 0;

    /**
     * Sets the actual search results.
     * @param array<EntityData> $results
     * @return $this
     */
    public function setResults(array $results): self
    {
        $this->results = $results;
        return $this;
    }

    /**
     * Returns the actual search results.
     * @return array<EntityData>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Sets the total number of results.
     * @param int $numberOfResults
     * @return $this
     */
    public function setNumberOfResults(int $numberOfResults): self
    {
        $this->numberOfResults = $numberOfResults;
        return $this;
    }

    /**
     * Returns the total number of results.
     * @return int
     */
    public function getNumberOfResults(): int
    {
        return $this->numberOfResults;
    }
}
