<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Exception;

use Throwable;

/**
 * The exception thrown when attempting to delete the active setting.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DeleteActiveSettingException extends PortalApiServerException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'Cannot delete the active setting.';

    /**
     * Initializes the exception.
     * @param Throwable|null $previous
     */
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE), 409, $previous);
    }
}
