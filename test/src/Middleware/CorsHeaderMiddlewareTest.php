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
        $allowedOrigins = ['abc', 'def'];
        $methods = 'ghi';

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var ResponseInterface&MockObject $response6 */
        $response6 = $this->createMock(ResponseInterface::class);

        /* @var ResponseInterface&MockObject $response5 */
        $response5 = $this->createMock(ResponseInterface::class);
        $response5->expects($this->once())
                  ->method('withHeader')
                  ->with($this->identicalTo('Access-Control-Allow-Origin'), $this->identicalTo('def'))
                  ->willReturn($response6);

        /* @var ResponseInterface&MockObject $response4 */
        $response4 = $this->createMock(ResponseInterface::class);
        $response4->expects($this->once())
                  ->method('withHeader')
                  ->with($this->identicalTo('Access-Control-Allow-Origin'), $this->identicalTo('abc'))
                  ->willReturn($response5);

        /* @var ResponseInterface&MockObject $response3 */
        $response3 = $this->createMock(ResponseInterface::class);
        $response3->expects($this->once())
                  ->method('hasHeader')
                  ->with($this->identicalTo('Allow'))
                  ->willReturn(true);
        $response3->expects($this->once())
                  ->method('getHeaderLine')
                  ->with($this->identicalTo('Allow'))
                  ->willReturn($methods);
        $response3->expects($this->once())
                  ->method('withHeader')
                  ->with($this->identicalTo('Access-Control-Allow-Methods'), $this->identicalTo($methods))
                  ->willReturn($response4);

        /* @var ResponseInterface&MockObject $response2 */
        $response2 = $this->createMock(ResponseInterface::class);
        $response2->expects($this->once())
                  ->method('withHeader')
                  ->with($this->identicalTo('Access-Control-Allow-Credentials'), $this->identicalTo('true'))
                  ->willReturn($response3);

        /* @var ResponseInterface&MockObject $response1 */
        $response1 = $this->createMock(ResponseInterface::class);
        $response1->expects($this->once())
                  ->method('withHeader')
                  ->with($this->identicalTo('Access-Control-Allow-Headers'), $this->identicalTo('Content-Type'))
                  ->willReturn($response2);

        /* @var RequestHandlerInterface&MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response1);

        $middleware = new CorsHeaderMiddleware($allowedOrigins);
        $result = $middleware->process($request, $handler);

        $this->assertSame($response6, $result);
    }
}
