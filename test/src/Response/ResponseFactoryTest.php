<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Response;

use FactorioItemBrowser\PortalApi\Server\Response\ResponseFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ResponseFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Response\ResponseFactory
 */
class ResponseFactoryTest extends TestCase
{
    /**
     * @param array<string> $mockedMethods
     * @return ResponseFactory&MockObject
     */
    private function createInstance(array $mockedMethods = []): ResponseFactory
    {
        return $this->getMockBuilder(ResponseFactory::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->getMock();
    }

    public function testCreateResponse(): void
    {
        $code = 123;
        $reason = 'abc';

        $instance = $this->createInstance();
        $result = $instance->createResponse($code, $reason);

        $this->assertSame($code, $result->getStatusCode());
        $this->assertSame($reason, $result->getReasonPhrase());
        $this->assertSame('3600', $result->getHeaderLine('Access-Control-Max-Age'));
    }
}
