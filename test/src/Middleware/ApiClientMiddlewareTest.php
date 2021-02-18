<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Middleware;

use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Middleware\ApiClientMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the ApiClientMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Middleware\ApiClientMiddleware
 */
class ApiClientMiddlewareTest extends TestCase
{
    /** @var ClientInterface&MockObject */
    private ClientInterface $apiClient;
    /** @var Setting&MockObject */
    private Setting $currentSetting;

    protected function setUp(): void
    {
        $this->apiClient = $this->createMock(ClientInterface::class);
        $this->currentSetting = $this->createMock(Setting::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return ApiClientMiddleware&MockObject
     */
    private function createInstance(array $mockedMethods = []): ApiClientMiddleware
    {
        return $this->getMockBuilder(ApiClientMiddleware::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->apiClient,
                        $this->currentSetting,
                    ])
                    ->getMock();
    }

    /**
     * @return array<mixed>
     */
    public function provideProcess(): array
    {
        return [
            ['78de8fa6-424b-479e-99c2-bb719eff1e0d', true, '78de8fa6-424b-479e-99c2-bb719eff1e0d'],
            ['78de8fa6-424b-479e-99c2-bb719eff1e0d', false, '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76'],
        ];
    }

    /**
     * @param string $combinationId
     * @param bool $hasData
     * @param string $expectedCombinationId
     * @dataProvider provideProcess
     */
    public function testProcess(string $combinationId, bool $hasData, string $expectedCombinationId): void
    {
        $combination = new Combination();
        $combination->setId(Uuid::fromString($combinationId));

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        $this->currentSetting->expects($this->any())
                             ->method('getCombination')
                             ->willReturn($combination);
        $this->currentSetting->expects($this->any())
                             ->method('getLocale')
                             ->willReturn('abc');
        $this->currentSetting->expects($this->any())
                             ->method('getHasData')
                             ->willReturn($hasData);

        $this->apiClient->expects($this->once())
                        ->method('setDefaults')
                        ->with($this->identicalTo($expectedCombinationId), $this->identicalTo('abc'));

        $instance = $this->createInstance();
        $result = $instance->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
