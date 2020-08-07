<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Exception;

use FactorioItemBrowser\PortalApi\Server\Exception\MissingCombinationIdException;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * The PHPUnit test of the MissingCombinationIdException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Exception\MissingCombinationIdException
 */
class MissingCombinationIdExceptionTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $expectedMessage = 'No valid combination id was present in the request header.';

        $previous = $this->createMock(Throwable::class);

        $exception = new MissingCombinationIdException($previous);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame(400, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
