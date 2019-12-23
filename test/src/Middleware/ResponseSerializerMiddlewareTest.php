<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Middleware;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\PortalApi\Server\Middleware\ResponseSerializerMiddleware;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;
use stdClass;

/**
 * The PHPUnit test of the ResponseSerializerMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Middleware\ResponseSerializerMiddleware
 */
class ResponseSerializerMiddlewareTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked serializer.
     * @var SerializerInterface&MockObject
     */
    protected $serializer;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $middleware = new ResponseSerializerMiddleware($this->serializer);

        $this->assertSame($this->serializer, $this->extractProperty($middleware, 'serializer'));
    }

    /**
     * Tests the process method.
     * @covers ::process
     */
    public function testProcess(): void
    {
        $serializedResponse = 'abc';

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var stdClass&MockObject $transfer */
        $transfer = $this->createMock(stdClass::class);
        /* @var TransferResponse&MockObject $modifiedResponse */
        $modifiedResponse = $this->createMock(TransferResponse::class);

        /* @var TransferResponse&MockObject $response */
        $response = $this->createMock(TransferResponse::class);
        $response->expects($this->once())
                 ->method('getTransfer')
                 ->willReturn($transfer);
        $response->expects($this->once())
                 ->method('withSerializedResponse')
                 ->with($this->identicalTo($serializedResponse))
                 ->willReturn($modifiedResponse);

        /* @var RequestHandlerInterface&MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        $this->serializer->expects($this->once())
                         ->method('serialize')
                         ->with($this->identicalTo($transfer), $this->identicalTo('json'))
                         ->willReturn($serializedResponse);

        $middleware = new ResponseSerializerMiddleware($this->serializer);
        $result = $middleware->process($request, $handler);

        $this->assertSame($modifiedResponse, $result);
    }

    /**
     * Tests the process method.
     * @covers ::process
     */
    public function testProcessWithoutTransferResponse(): void
    {
        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /* @var RequestHandlerInterface&MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        $this->serializer->expects($this->never())
                         ->method('serialize');

        $middleware = new ResponseSerializerMiddleware($this->serializer);
        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
