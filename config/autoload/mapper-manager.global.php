<?php

/**
 * The configuration of the mapper manager.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server;

use BluePsyduck\MapperManager\Constant\ConfigKey;

return [
    ConfigKey::MAIN => [
        ConfigKey::MAPPERS => [
            Mapper\GenericEntityMapper::class,
            Mapper\ItemListResponseMapper::class,
            Mapper\ItemRecipesMapper::class,
            Mapper\MachineMapper::class,
            Mapper\ModMapper::class,
            Mapper\RecipeDetailsMapper::class,
            Mapper\RecipeItemMapper::class,
            Mapper\RecipeMachinesMapper::class,
            Mapper\RecipeMapper::class,
            Mapper\RecipeToEntityMapper::class,
            Mapper\SearchQueryResponseMapper::class,
            Mapper\SettingMapper::class,
            Mapper\SidebarEntityDataMapper::class,
            Mapper\SidebarEntityMapper::class,
        ],
    ],
];
