<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Exception;

use FactorioItemBrowser\PortalApi\Server\Exception\ApiEndpointNotFoundServerException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * The PHPUnit test of the ApiEndpointNotFoundServerException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Exception\ApiEndpointNotFoundServerException
 */
class ApiEndpointNotFoundServerExceptionTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $endpoint = 'abc';
        $expectedMessage = 'API endpoint not found: abc';

        /* @var Throwable&MockObject $previous */
        $previous = $this->createMock(Throwable::class);

        $exception = new ApiEndpointNotFoundServerException($endpoint, $previous);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame(400, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
