<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Exception;

use Exception;
use FactorioItemBrowser\PortalApi\Server\Exception\DeleteActiveSettingException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the DeleteActiveSettingException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Exception\DeleteActiveSettingException
 */
class DeleteActiveSettingExceptionTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $expectedMessage = 'Cannot delete the active setting.';

        /* @var Exception&MockObject $previous */
        $previous = $this->createMock(Exception::class);

        $exception = new DeleteActiveSettingException($previous);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame(409, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
