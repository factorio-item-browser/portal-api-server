<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Exception;

use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Exception\ConnectionException;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the FailedApiRequestException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException
 */
class FailedApiRequestExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $apiClientException = new ClientException('abc');
        $expectedMessage = 'Request to the API failed: abc';

        $exception = new FailedApiRequestException($apiClientException);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame(500, $exception->getCode());
        $this->assertSame($apiClientException, $exception->getPrevious());
    }

    public function testConstructWithTimeoutException(): void
    {
        $apiClientException = new ConnectionException('abc', 'def');
        $expectedMessage = 'Request to the API failed: Failed to connect to the server: abc';

        $exception = new FailedApiRequestException($apiClientException);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame(503, $exception->getCode());
        $this->assertSame($apiClientException, $exception->getPrevious());
    }
}
