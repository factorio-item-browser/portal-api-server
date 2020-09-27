<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Exception;

use Ramsey\Uuid\UuidInterface;
use Throwable;

/**
 * The exception thrown when an invalid setting has been encountered.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MissingSettingException extends PortalApiServerException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'Setting with combination %s was not found for the current session.';

    /**
     * Initializes the exception.
     * @param UuidInterface $combinationId
     * @param Throwable|null $previous
     */
    public function __construct(UuidInterface $combinationId, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, $combinationId->toString()), 400, $previous);
    }
}
