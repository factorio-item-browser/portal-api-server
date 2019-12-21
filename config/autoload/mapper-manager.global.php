<?php

declare(strict_types=1);

/**
 * The configuration of the mapper manager.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\PortalApi\Server;

use BluePsyduck\MapperManager\Constant\ConfigKey;

return [
    ConfigKey::MAIN => [
        ConfigKey::MAPPERS => [
            Mapper\GenericEntityMapper::class,
            Mapper\RecipeItemMapper::class,
            Mapper\RecipeMapper::class,
            Mapper\SearchQueryResponseMapper::class,
        ],
    ],
];
