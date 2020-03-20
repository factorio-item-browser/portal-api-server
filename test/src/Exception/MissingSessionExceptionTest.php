<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Exception;

use FactorioItemBrowser\PortalApi\Server\Exception\MissingSessionException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * The PHPUnit test of the MissingSessionException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Exception\MissingSessionException
 */
class MissingSessionExceptionTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $expectedMessage = 'No valid session was found.';

        /* @var Throwable&MockObject $previous */
        $previous = $this->createMock(Throwable::class);

        $exception = new MissingSessionException($previous);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame(401, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
