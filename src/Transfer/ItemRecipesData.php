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
    public string $type = '';
    public string $name = '';
    public string $label = '';
    public string $description = '';

    /** @var array<EntityData> */
    public array $results = [];
    public int $numberOfResults = 0;
}
