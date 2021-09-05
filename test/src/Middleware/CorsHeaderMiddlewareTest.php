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
 * @covers \FactorioItemBrowser\PortalApi\Server\Middleware\CorsHeaderMiddleware
 */
class CorsHeaderMiddlewareTest extends TestCase
{
    use ReflectionTrait;

    /** @var array<string> */
    private array $allowedOrigins = ['abc', 'def'];

    /**
     * @param array<string> $mockedMethods
     * @return CorsHeaderMiddleware&MockObject
     */
    private function createInstance(array $mockedMethods = []): CorsHeaderMiddleware
    {
        return $this->getMockBuilder(CorsHeaderMiddleware::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->allowedOrigins,
                    ])
                    ->getMock();
    }

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

        $response2 = $this->createMock(ResponseInterface::class);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
                 ->method('withHeader')
                 ->with($this->identicalTo('Access-Control-Max-Age'), $this->identicalTo('3600'))
                 ->willReturn($response2);

        $responseWithHeaders = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        $instance = $this->createInstance(['isOriginAllowed', 'addHeaders']);
        $instance->expects($this->once())
                 ->method('isOriginAllowed')
                 ->with($this->identicalTo($origin))
                 ->willReturn(true);
        $instance->expects($this->once())
                 ->method('addHeaders')
                 ->with($this->identicalTo($response2), $this->identicalTo($origin))
                 ->willReturn($responseWithHeaders);

        $result = $instance->process($request, $handler);

        $this->assertSame($responseWithHeaders, $result);
    }

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

        $response2 = $this->createMock(ResponseInterface::class);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
                 ->method('withHeader')
                 ->with($this->identicalTo('Access-Control-Max-Age'), $this->identicalTo('3600'))
                 ->willReturn($response2);

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

        $this->assertSame($response2, $result);
    }

    /**
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
     * @param array<string> $allowedOrigins
     * @param string $origin
     * @param bool $expectedResult
     * @throws ReflectionException
     * @dataProvider provideIsOriginAllowed
     */
    public function testIsOriginAllowed(array $allowedOrigins, string $origin, bool $expectedResult): void
    {
        $this->allowedOrigins = $allowedOrigins;

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'isOriginAllowed', $origin);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
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

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'addHeaders', $response1, $origin);

        $this->assertSame($response5, $result);
    }
}
