<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Exception;

use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Exception\ConnectionException;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the FailedApiRequestException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException
 */
class FailedApiRequestExceptionTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $apiClientException = new ApiClientException('abc');
        $expectedMessage = 'Request to the API failed: abc';

        $exception = new FailedApiRequestException($apiClientException);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame(500, $exception->getCode());
        $this->assertSame($apiClientException, $exception->getPrevious());
    }

    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstructWithTimeoutException(): void
    {
        $apiClientException = new ConnectionException('abc', 'def');
        $expectedMessage = 'Request to the API failed: abc';

        $exception = new FailedApiRequestException($apiClientException);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame(503, $exception->getCode());
        $this->assertSame($apiClientException, $exception->getPrevious());
    }
}
