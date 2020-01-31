<?php

declare(strict_types=1);

/**
 * The configuration of the Doctrine integration.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\PortalApi\Server;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Ramsey\Uuid\Doctrine\UuidBinaryType;

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'doctrine_mapping_types' => [
                    UuidBinaryType::NAME => Types::BINARY,
                    'enum' => 'string',
                ],
            ],
        ],
        'driver' => [
            'orm_default' => [
                'class' => SimplifiedXmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    'config/doctrine' => 'FactorioItemBrowser\PortalApi\Server\Entity',
                ],
            ],
        ],
        'migrations_configuration' => [
            'orm_default' => [
                'directory' => 'data/migrations',
                'name'      => 'FactorioItemBrowser Portal API Server Database Migrations',
                'namespace' => 'FactorioItemBrowser\PortalApi\Server\Migrations',
                'table'     => '_Migrations',
            ],
        ],
        'types' => [
            Doctrine\Type\EnumTypeRecipeMode::NAME => Doctrine\Type\EnumTypeRecipeMode::class,
            Doctrine\Type\EnumTypeSidebarEntityType::NAME => Doctrine\Type\EnumTypeSidebarEntityType::class,
            Doctrine\Type\TimestampType::NAME => Doctrine\Type\TimestampType::class,

            UuidBinaryType::NAME => UuidBinaryType::class,
        ],
    ],
];
