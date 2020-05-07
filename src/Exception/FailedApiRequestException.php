<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Exception;

use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Exception\ConnectionException;

/**
 * The exception thrown when an API request failed.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class FailedApiRequestException extends PortalApiServerException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'Request to the API failed: %s';

    /**
     * Initializes the exception.
     * @param ApiClientException $apiClientException
     */
    public function __construct(ApiClientException $apiClientException)
    {
        $errorCode = $apiClientException instanceof ConnectionException ? 503 : 500;
        parent::__construct(sprintf(self::MESSAGE, $apiClientException->getMessage()), $errorCode, $apiClientException);
    }
}
