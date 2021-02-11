<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Middleware;

use FactorioItemBrowser\PortalApi\Server\Middleware\ResponseSerializerMiddleware;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The PHPUnit test of the ResponseSerializerMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Middleware\ResponseSerializerMiddleware
 */
class ResponseSerializerMiddlewareTest extends TestCase
{
    /** @var SerializerInterface&MockObject */
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return ResponseSerializerMiddleware&MockObject
     */
    private function createInstance(array $mockedMethods = []): ResponseSerializerMiddleware
    {
        return $this->getMockBuilder(ResponseSerializerMiddleware::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->serializer,
                    ])
                    ->getMock();
    }

    public function testProcess(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response2 = $this->createMock(TransferResponse::class);

        $response1 = $this->createMock(TransferResponse::class);
        $response1->expects($this->once())
                  ->method('withSerializer')
                  ->with($this->identicalTo($this->serializer))
                  ->willReturn($response2);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response1);

        $instance = $this->createInstance();
        $result = $instance->process($request, $handler);

        $this->assertSame($response2, $result);
    }

    public function testProcessWithoutClientResponse(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        $instance = $this->createInstance();
        $result = $instance->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
