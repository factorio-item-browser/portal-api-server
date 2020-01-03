<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Exception;

use Throwable;

/**
 * The exception thrown when an entity unknown to the API server is encountered.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UnknownEntityException extends PortalApiServerException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'The %s %s is not known.';

    /**
     * Initializes the exception.
     * @param string $type
     * @param string $name
     * @param Throwable|null $previous
     */
    public function __construct(string $type, string $name, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, $type, $name), 404, $previous);
    }
}
