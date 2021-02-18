<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Exception;

use FactorioItemBrowser\CombinationApi\Client\Exception\ClientException;
use FactorioItemBrowser\CombinationApi\Client\Exception\ConnectionException;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedCombinationApiException;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the FailedCombinationApiException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Exception\FailedCombinationApiException
 */
class FailedCombinationApiExceptionTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    public function provide(): array
    {
        return [
            [
                new ClientException('abc'),
                'Request to the Combination API failed: abc',
                500,
            ],
            [
                new ConnectionException('abc', 'def'),
                'Request to the Combination API failed: Failed to connect to the server: abc',
                503,
            ],
        ];
    }

    /**
     * @param ClientException $exception
     * @param string $expectedMessage
     * @param int $expectedCode
     * @dataProvider provide
     */
    public function test(ClientException $exception, string $expectedMessage, int $expectedCode): void
    {
        $instance = new FailedCombinationApiException($exception);

        $this->assertSame($expectedMessage, $instance->getMessage());
        $this->assertSame($expectedCode, $instance->getCode());
        $this->assertSame($exception, $instance->getPrevious());
    }
}
