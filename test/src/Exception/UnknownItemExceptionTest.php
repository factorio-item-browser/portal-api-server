<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Exception;

use FactorioItemBrowser\PortalApi\Server\Exception\UnknownItemException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * The PHPUnit test of the UnknownItemException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Exception\UnknownItemException
 */
class UnknownItemExceptionTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $type = 'abc';
        $name = 'def';
        $expectedMessage = 'The abc def is not known.';

        /* @var Throwable&MockObject $previous */
        $previous = $this->createMock(Throwable::class);

        $exception = new UnknownItemException($type, $name, $previous);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame(404, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
