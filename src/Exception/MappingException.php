<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Exception;

use BluePsyduck\MapperManager\Exception\MapperException;

/**
 * The exception thrown when the mapping process failed.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MappingException extends PortalApiServerException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'Failed to map the response: %s';

    /**
     * Initializes the exception.
     * @param MapperException $mapperException
     */
    public function __construct(MapperException $mapperException)
    {
        parent::__construct(sprintf(self::MESSAGE, $mapperException->getMessage()), 500, $mapperException);
    }
}
