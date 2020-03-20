<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Exception;

use Throwable;

/**
 * The exception thrown when no valid session has been found.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MissingSessionException extends PortalApiServerException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'No valid session was found.';

    /**
     * Initializes the exception.
     * @param Throwable|null $previous
     */
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, 401, $previous);
    }
}
