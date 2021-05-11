<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Style;

use FactorioItemBrowser\Api\Client\Exception\ClientException;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\Style\IconsHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\IconsStyleFetcher;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleData;
use FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleRequestData;
use GuzzleHttp\Promise\PromiseInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The PHPUnit test of the IconsHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Handler\Style\IconsHandler
 */
class IconsHandlerTest extends TestCase
{
    /** @var Setting&MockObject */
    private Setting $currentSetting;
    /** @var IconsStyleFetcher&MockObject */
    private IconsStyleFetcher $iconsStyleFetcher;

    protected function setUp(): void
    {
        $this->currentSetting = $this->createMock(Setting::class);
        $this->iconsStyleFetcher = $this->createMock(IconsStyleFetcher::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return IconsHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): IconsHandler
    {
        return $this->getMockBuilder(IconsHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->currentSetting,
                        $this->iconsStyleFetcher,
                    ])
                    ->getMock();
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandle(): void
    {
        $requestData = new IconsStyleRequestData();
        $requestData->cssSelector = 'def';

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn($requestData);

        $iconsPromise = $this->createMock(PromiseInterface::class);
        $transfer = new IconsStyleData();
        $transfer->style = 'abc';

        $this->iconsStyleFetcher->expects($this->once())
                                ->method('request')
                                ->with(
                                    $this->identicalTo($this->currentSetting),
                                    $this->identicalTo($requestData->entities),
                                )
                                ->willReturn($iconsPromise);
        $this->iconsStyleFetcher->expects($this->once())
                                ->method('process')
                                ->with($this->identicalTo('def'), $this->identicalTo($iconsPromise))
                                ->willReturn($transfer);
        $this->iconsStyleFetcher->expects($this->once())
                                ->method('addMissingEntities')
                                ->with(
                                    $this->identicalTo($transfer->processedEntities),
                                    $this->identicalTo($requestData->entities),
                                );

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        /* @var TransferResponse $result */
        $this->assertSame($transfer, $result->getTransfer());
    }

    /**
     * @throws PortalApiServerException
     */
    public function testHandleWithApiException(): void
    {
        $requestData = new IconsStyleRequestData();
        $requestData->cssSelector = 'def';

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn($requestData);

        $this->iconsStyleFetcher->expects($this->once())
                                ->method('request')
                                ->with(
                                    $this->identicalTo($this->currentSetting),
                                    $this->identicalTo($requestData->entities),
                                )
                                ->willThrowException($this->createMock(ClientException::class));

        $this->expectException(FailedApiRequestException::class);

        $instance = $this->createInstance();
        $instance->handle($request);
    }
}
