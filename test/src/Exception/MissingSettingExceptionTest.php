<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Exception;

use FactorioItemBrowser\PortalApi\Server\Exception\MissingSettingException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Throwable;

/**
 * The PHPUnit test of the MissingSettingException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Exception\MissingSettingException
 */
class MissingSettingExceptionTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $combinationId = Uuid::fromString('800a2e58-034d-414e-8bb0-056bb9d5b4b0');
        $expectedMessage =
            'Setting with combination 800a2e58-034d-414e-8bb0-056bb9d5b4b0 was not found for the current session.';

        $previous = $this->createMock(Throwable::class);

        $exception = new MissingSettingException($combinationId, $previous);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame(400, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
