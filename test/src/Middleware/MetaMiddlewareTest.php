<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Middleware;

use FactorioItemBrowser\PortalApi\Server\Middleware\MetaMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The PHPUnit test of the MetaMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Middleware\MetaMiddleware
 */
class MetaMiddlewareTest extends TestCase
{
    private string $version = '1.2.3';

    /**
     * @param array<string> $mockedMethods
     * @return MetaMiddleware&MockObject
     */
    private function createInstance(array $mockedMethods = []): MetaMiddleware
    {
        return $this->getMockBuilder(MetaMiddleware::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->version,
                    ])
                    ->getMock();
    }

    public function testProcess(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->exactly(2))
                 ->method('withHeader')
                 ->withConsecutive(
                     [$this->identicalTo('Version'), $this->identicalTo('1.2.3')],
                     [$this->identicalTo('Runtime'), $this->isType('string')]
                 )
                 ->willReturnSelf();

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
