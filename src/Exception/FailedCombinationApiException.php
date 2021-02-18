<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Exception;

use FactorioItemBrowser\CombinationApi\Client\Exception\ClientException;
use FactorioItemBrowser\CombinationApi\Client\Exception\ConnectionException;

/**
 * The exception thrown when a request to the Portal API failed.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class FailedCombinationApiException extends PortalApiServerException
{
    private const MESSAGE = 'Request to the Combination API failed: %s';

    public function __construct(ClientException $apiClientException)
    {
        $errorCode = $apiClientException instanceof ConnectionException ? 503 : 500;
        parent::__construct(sprintf(self::MESSAGE, $apiClientException->getMessage()), $errorCode, $apiClientException);
    }
}
