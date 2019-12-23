<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\EntityData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SearchResultsData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the SearchResultsData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\SearchResultsData
 */
class SearchResultsDataTest extends TestCase
{
    /**
     * Tests the setting and getting the query.
     * @covers ::getQuery
     * @covers ::setQuery
     */
    public function testSetAndGetQuery(): void
    {
        $query = 'abc';
        $transfer = new SearchResultsData();

        $this->assertSame($transfer, $transfer->setQuery($query));
        $this->assertSame($query, $transfer->getQuery());
    }

    /**
     * Tests the setting and getting the results.
     * @covers ::getResults
     * @covers ::setResults
     */
    public function testSetAndGetResults(): void
    {
        $results = [
            $this->createMock(EntityData::class),
            $this->createMock(EntityData::class),
        ];
        $transfer = new SearchResultsData();

        $this->assertSame($transfer, $transfer->setResults($results));
        $this->assertSame($results, $transfer->getResults());
    }

    /**
     * Tests the setting and getting the number of results.
     * @covers ::getNumberOfResults
     * @covers ::setNumberOfResults
     */
    public function testSetAndGetNumberOfResults(): void
    {
        $numberOfResults = 42;
        $transfer = new SearchResultsData();

        $this->assertSame($transfer, $transfer->setNumberOfResults($numberOfResults));
        $this->assertSame($numberOfResults, $transfer->getNumberOfResults());
    }
}
