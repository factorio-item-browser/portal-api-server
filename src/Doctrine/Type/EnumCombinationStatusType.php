<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Doctrine\Type;

use FactorioItemBrowser\PortalApi\Server\Constant\CombinationStatus;

/**
 * The enum of the combination status.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class EnumCombinationStatusType extends AbstractEnumType
{
    /**
     * The name of the enum.
     */
    public const NAME = 'enum_combination_status';

    /**
     * The values of the num.
     */
    public const VALUES = [
        CombinationStatus::PENDING,
        CombinationStatus::AVAILABLE,
        CombinationStatus::ERRORED,
    ];
}
