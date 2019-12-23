<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Response;

use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * The PHPUnit test of the TransferResponse class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Response\TransferResponse
 */
class TransferResponseTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     * @covers ::getTransfer
     */
    public function testConstruct(): void
    {
        /* @var stdClass&MockObject $transfer */
        $transfer = $this->createMock(stdClass::class);
        $statusCode = 123;
        $headers = [
            'abc' => 'def',
        ];

        $response = new TransferResponse($transfer, $statusCode, $headers);

        $this->assertSame($statusCode, $response->getStatusCode());
        $this->assertSame('def', $response->getHeaderLine('abc'));
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertSame($transfer, $response->getTransfer());
    }

    /**
     * Tests the withSerializedResponse method.
     * @covers ::withSerializedResponse
     */
    public function testWithSerializedResponse(): void
    {
        /* @var stdClass&MockObject $transfer */
        $transfer = $this->createMock(stdClass::class);
        $statusCode = 123;
        $headers = [
            'abc' => 'def',
        ];
        $serializedResponse = 'ghi';

        $response = new TransferResponse($transfer, $statusCode, $headers);
        $result = $response->withSerializedResponse($serializedResponse);

        $this->assertNotSame($response, $result);
        $this->assertSame($statusCode, $result->getStatusCode());
        $this->assertSame('def', $result->getHeaderLine('abc'));
        $this->assertSame('application/json', $result->getHeaderLine('Content-Type'));
        $this->assertSame($transfer, $result->getTransfer());
        $this->assertSame($serializedResponse, $result->getBody()->getContents());
    }
}
