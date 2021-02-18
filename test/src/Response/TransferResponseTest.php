<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Response;

use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * The PHPUnit test of the TransferResponse class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Response\TransferResponse
 */
class TransferResponseTest extends TestCase
{
    public function testWithSerializer(): void
    {
        $transfer = new stdClass();
        $transfer->foo = 'bar';
        $statusCode = 512;
        $headers = ['abc' => 'def'];
        $body = 'ghi';

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
                   ->method('serialize')
                   ->with($this->identicalTo($transfer), $this->identicalTo('json'))
                   ->willReturn($body);

        $instance = new TransferResponse($transfer, $statusCode, $headers);
        $result = $instance->withSerializer($serializer);

        $this->assertSame($transfer, $instance->getTransfer());
        $this->assertSame($statusCode, $result->getStatusCode());
        $this->assertSame('def', $result->getHeaderLine('abc'));
        $this->assertSame('application/json', $result->getHeaderLine('Content-Type'));
        $this->assertSame($body, $result->getBody()->getContents());
    }
}
