<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Middleware;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
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
     * The mocked api client factory.
     * @var ApiClientFactory&MockObject
     */
    protected $apiClientFactory;

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

        $this->apiClientFactory = $this->createMock(ApiClientFactory::class);
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
        $middleware = new ApiClientMiddleware($this->apiClientFactory, $this->apiClient, $this->currentSetting);

        $this->assertSame($this->apiClient, $this->extractProperty($middleware, 'apiClient'));
        $this->assertSame($this->currentSetting, $this->extractProperty($middleware, 'currentSetting'));
    }

    /**
     * Tests the process method.
     * @covers ::process
     */
    public function testProcess(): void
    {
        $newApiAuthorizationToken = 'abc';

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
                             ->method('setApiAuthorizationToken')
                             ->with($this->identicalTo($newApiAuthorizationToken));

        $this->apiClient->expects($this->once())
                        ->method('getAuthorizationToken')
                        ->willReturn($newApiAuthorizationToken);

        $this->apiClientFactory->expects($this->once())
                               ->method('configure')
                               ->with($this->identicalTo($this->apiClient), $this->identicalTo($this->currentSetting));

        $middleware = new ApiClientMiddleware($this->apiClientFactory, $this->apiClient, $this->currentSetting);
        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
