<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The response containing the search results data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SearchResultsData
{
    public string $query = '';
    /** @var array<EntityData> */
    public array $results = [];
    public int $numberOfResults = 0;
}
