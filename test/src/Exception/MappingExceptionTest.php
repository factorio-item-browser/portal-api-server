<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Exception;

use BluePsyduck\MapperManager\Exception\MissingMapperException;
use FactorioItemBrowser\PortalApi\Server\Exception\MappingException;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the MappingException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Exception\MappingException
 */
class MappingExceptionTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $mapperException = new MissingMapperException('abc', 'def');

        $expectedMessagePart = 'Failed to map the response:';

        $exception = new MappingException($mapperException);

        $this->assertStringContainsString($expectedMessagePart, $exception->getMessage());
        $this->assertSame(500, $exception->getCode());
        $this->assertSame($mapperException, $exception->getPrevious());
    }
}
