<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Middleware;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\PortalApi\Server\Middleware\CorsHeaderMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;

/**
 * The PHPUnit test of the CorsHeaderMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Middleware\CorsHeaderMiddleware
 */
class CorsHeaderMiddlewareTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $allowedOrigins = ['abc', 'def'];

        $middleware = new CorsHeaderMiddleware($allowedOrigins);

        $this->assertSame($allowedOrigins, $this->extractProperty($middleware, 'allowedOrigins'));
    }

    /**
     * Tests the process method.
     * @covers ::process
     */
    public function testProcess(): void
    {
        $origin = 'abc';
        $serverParams = [
            'HTTP_ORIGIN' => $origin,
        ];

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getServerParams')
                ->willReturn($serverParams);

        $response = $this->createMock(ResponseInterface::class);
        $responseWithHeaders = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        $middleware = $this->getMockBuilder(CorsHeaderMiddleware::class)
                           ->onlyMethods(['isOriginAllowed', 'AddHeaders'])
                           ->setConstructorArgs([['foo', 'bar']])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('isOriginAllowed')
                   ->with($this->identicalTo($origin))
                   ->willReturn(true);
        $middleware->expects($this->once())
                   ->method('addHeaders')
                   ->with($this->identicalTo($response), $this->identicalTo($origin))
                   ->willReturn($responseWithHeaders);

        $result = $middleware->process($request, $handler);

        $this->assertSame($responseWithHeaders, $result);
    }

    /**
     * Tests the process method.
     * @covers ::process
     */
    public function testProcessWithoutHeaders(): void
    {
        $origin = 'abc';
        $serverParams = [
            'HTTP_ORIGIN' => $origin,
        ];

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getServerParams')
                ->willReturn($serverParams);

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        $middleware = $this->getMockBuilder(CorsHeaderMiddleware::class)
                           ->onlyMethods(['isOriginAllowed', 'AddHeaders'])
                           ->setConstructorArgs([['foo', 'bar']])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('isOriginAllowed')
                   ->with($this->identicalTo($origin))
                   ->willReturn(false);
        $middleware->expects($this->never())
                   ->method('addHeaders');

        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    /**
     * Provides the data for the isOriginAllowed test.
     * @return array<mixed>
     */
    public function provideIsOriginAllowed(): array
    {
        $allowedOrigins = [
            '#^foo(\.bar)?$#',
            '#^baz$#',
        ];

        return [
            [$allowedOrigins, 'foo', true],
            [$allowedOrigins, 'bar', false],
            [$allowedOrigins, 'baz', true],
            [$allowedOrigins, 'foo.bar', true],
            [$allowedOrigins, 'foo.baz', false],
        ];
    }

    /**
     * Tests the isOriginAllowed method.
     * @param array<string> $allowedOrigins
     * @param string $origin
     * @param bool $expectedResult
     * @throws ReflectionException
     * @covers ::isOriginAllowed
     * @dataProvider provideIsOriginAllowed
     */
    public function testIsOriginAllowed(array $allowedOrigins, string $origin, bool $expectedResult): void
    {
        $middleware = new CorsHeaderMiddleware($allowedOrigins);
        $result = $this->invokeMethod($middleware, 'isOriginAllowed', $origin);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the addHeaders method.
     * @throws ReflectionException
     * @covers ::addHeaders
     */
    public function testAddHeaders(): void
    {
        $origin = 'abc';
        $allow = 'def';

        $response5 = $this->createMock(ResponseInterface::class);

        $response4 = $this->createMock(ResponseInterface::class);
        $response4->expects($this->once())
                  ->method('hasHeader')
                  ->with($this->identicalTo('Allow'))
                  ->willReturn(true);
        $response4->expects($this->once())
                  ->method('getHeaderLine')
                  ->with($this->identicalTo('Allow'))
                  ->willReturn($allow);
        $response4->expects($this->once())
                  ->method('withHeader')
                  ->with($this->identicalTo('Access-Control-Allow-Methods'), $this->identicalTo($allow))
                  ->willReturn($response5);

        $response3 = $this->createMock(ResponseInterface::class);
        $response3->expects($this->once())
                  ->method('withHeader')
                  ->with($this->identicalTo('Access-Control-Allow-Origin'), $this->identicalTo($origin))
                  ->willReturn($response4);

        $response2 = $this->createMock(ResponseInterface::class);
        $response2->expects($this->once())
                  ->method('withHeader')
                  ->with($this->identicalTo('Access-Control-Allow-Credentials'), $this->identicalTo('true'))
                  ->willReturn($response3);

        $response1 = $this->createMock(ResponseInterface::class);
        $response1->expects($this->once())
                  ->method('withHeader')
                  ->with(
                      $this->identicalTo('Access-Control-Allow-Headers'),
                      $this->identicalTo('Combination-Id,Content-Type')
                  )
                  ->willReturn($response2);

        $middleware = new CorsHeaderMiddleware(['foo', 'bar']);
        $result = $this->invokeMethod($middleware, 'addHeaders', $response1, $origin);

        $this->assertSame($response5, $result);
    }
}
