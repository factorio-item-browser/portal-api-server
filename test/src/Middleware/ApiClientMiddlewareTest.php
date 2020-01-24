<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Middleware;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Middleware\ApiClientMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;

/**
 * The PHPUnit test of the ApiClientMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Middleware\ApiClientMiddleware
 */
class ApiClientMiddlewareTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked api client.
     * @var ApiClientInterface&MockObject
     */
    protected $apiClient;

    /**
     * The mocked current setting.
     * @var Setting&MockObject
     */
    protected $currentSetting;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClient = $this->createMock(ApiClientInterface::class);
        $this->currentSetting = $this->createMock(Setting::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $middleware = new ApiClientMiddleware($this->apiClient, $this->currentSetting);

        $this->assertSame($this->apiClient, $this->extractProperty($middleware, 'apiClient'));
        $this->assertSame($this->currentSetting, $this->extractProperty($middleware, 'currentSetting'));
    }

    /**
     * Tests the process method.
     * @covers ::process
     */
    public function testProcess(): void
    {
        $locale = 'abc';
        $modNames = ['def', 'ghi'];
        $apiAuthorizationToken = 'jkl';
        $newApiAuthorizationToken = 'mno';

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

        $this->currentSetting->expects($this->once())
                             ->method('getLocale')
                             ->willReturn($locale);
        $this->currentSetting->expects($this->once())
                             ->method('getModNames')
                             ->willReturn($modNames);
        $this->currentSetting->expects($this->once())
                             ->method('getApiAuthorizationToken')
                             ->willReturn($apiAuthorizationToken);
        $this->currentSetting->expects($this->once())
                             ->method('setApiAuthorizationToken')
                             ->with($this->identicalTo($newApiAuthorizationToken));

        $this->apiClient->expects($this->once())
                        ->method('setLocale')
                        ->with($this->identicalTo($locale));
        $this->apiClient->expects($this->once())
                        ->method('setModNames')
                        ->with($this->identicalTo($modNames));
        $this->apiClient->expects($this->once())
                        ->method('setAuthorizationToken')
                        ->with($this->identicalTo($apiAuthorizationToken));
        $this->apiClient->expects($this->once())
                        ->method('getAuthorizationToken')
                        ->willReturn($newApiAuthorizationToken);

        $middleware = new ApiClientMiddleware($this->apiClient, $this->currentSetting);
        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
