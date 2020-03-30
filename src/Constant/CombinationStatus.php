<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Constant;

/**
 * The interface holding the combination status values.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface CombinationStatus
{
    /**
     * The combination is currently not known to the system.
     */
    public const UNKNOWN = 'unknown';

    /**
     * The combination data is still pending to be exported.
     */
    public const PENDING = 'pending';

    /**
     * The combination data is available.
     */
    public const AVAILABLE = 'available';

    /**
     * The combination data has errored during the export and cannot be made available.
     */
    public const ERRORED = 'errored';
}
