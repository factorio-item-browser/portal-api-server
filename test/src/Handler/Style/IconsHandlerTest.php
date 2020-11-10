<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Style;

use BluePsyduck\TestHelper\ReflectionTrait;
use Exception;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\InvalidRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\Style\IconsHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\IconsStyleFetcher;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleData;
use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use ReflectionException;

/**
 * The PHPUnit test of the IconsHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Style\IconsHandler
 */
class IconsHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked current setting.
     * @var Setting&MockObject
     */
    protected $currentSetting;

    /**
     * The mocked icons style fetcher.
     * @var IconsStyleFetcher&MockObject
     */
    protected $iconsStyleFetcher;

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

        $this->currentSetting = $this->createMock(Setting::class);
        $this->iconsStyleFetcher = $this->createMock(IconsStyleFetcher::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new IconsHandler($this->currentSetting, $this->iconsStyleFetcher, $this->serializer);

        $this->assertSame($this->currentSetting, $this->extractProperty($handler, 'currentSetting'));
        $this->assertSame($this->iconsStyleFetcher, $this->extractProperty($handler, 'iconsStyleFetcher'));
        $this->assertSame($this->serializer, $this->extractProperty($handler, 'serializer'));
    }

    /**
     * Tests the handle method.
     * @throws PortalApiServerException
     * @covers ::handle
     */
    public function testHandle(): void
    {
        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var NamesByTypes&MockObject $namesByTypes */
        $namesByTypes = $this->createMock(NamesByTypes::class);
        /* @var IconsStyleData&MockObject $iconsStyleData */
        $iconsStyleData = $this->createMock(IconsStyleData::class);

        $this->iconsStyleFetcher->expects($this->once())
                                ->method('request')
                                ->with($this->identicalTo($this->currentSetting), $this->identicalTo($namesByTypes));
        $this->iconsStyleFetcher->expects($this->once())
                                ->method('process')
                                ->willReturn($iconsStyleData);

        /* @var IconsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(IconsHandler::class)
                        ->onlyMethods(['parseRequestBody'])
                        ->setConstructorArgs([$this->currentSetting, $this->iconsStyleFetcher, $this->serializer])
                        ->getMock();
        $handler->expects($this->once())
                ->method('parseRequestBody')
                ->with($this->identicalTo($request))
                ->willReturn($namesByTypes);

        /* @var TransferResponse $result */
        $result = $handler->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        $this->assertSame($iconsStyleData, $result->getTransfer());
    }

    /**
     * Tests the handle method.
     * @throws PortalApiServerException
     * @covers ::handle
     */
    public function testHandleWithException(): void
    {
        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var NamesByTypes&MockObject $namesByTypes */
        $namesByTypes = $this->createMock(NamesByTypes::class);

        $this->iconsStyleFetcher->expects($this->once())
                                ->method('request')
                                ->with($this->identicalTo($this->currentSetting), $this->identicalTo($namesByTypes))
                                ->willThrowException($this->createMock(ApiClientException::class));

        $this->expectException(FailedApiRequestException::class);

        /* @var IconsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(IconsHandler::class)
                        ->onlyMethods(['parseRequestBody'])
                        ->setConstructorArgs([$this->currentSetting, $this->iconsStyleFetcher, $this->serializer])
                        ->getMock();
        $handler->expects($this->once())
                ->method('parseRequestBody')
                ->with($this->identicalTo($request))
                ->willReturn($namesByTypes);

        $handler->handle($request);
    }

    /**
     * Tests the parseRequestBody method.
     * @throws ReflectionException
     * @covers ::parseRequestBody
     */
    public function testParseRequestBody(): void
    {
        $requestBody = 'abc';

        /* @var NamesByTypes&MockObject $deserializedRequestBody */
        $deserializedRequestBody = $this->createMock(NamesByTypes::class);

        /* @var StreamInterface&MockObject $body */
        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
             ->method('getContents')
             ->willReturn($requestBody);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getBody')
                ->willReturn($body);

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($requestBody),
                             $this->identicalTo(NamesByTypes::class),
                             $this->identicalTo('json')
                         )
                         ->willReturn($deserializedRequestBody);

        $handler = new IconsHandler($this->currentSetting, $this->iconsStyleFetcher, $this->serializer);
        $result = $this->invokeMethod($handler, 'parseRequestBody', $request);

        $this->assertSame($deserializedRequestBody, $result);
    }

    /**
     * Tests the parseRequestBody method.
     * @throws ReflectionException
     * @covers ::parseRequestBody
     */
    public function testParseRequestBodyWithException(): void
    {
        $requestBody = 'abc';

        /* @var StreamInterface&MockObject $body */
        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
             ->method('getContents')
             ->willReturn($requestBody);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getBody')
                ->willReturn($body);

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($requestBody),
                             $this->identicalTo(NamesByTypes::class),
                             $this->identicalTo('json')
                         )
                         ->willThrowException($this->createMock(Exception::class));

        $this->expectException(InvalidRequestException::class);

        $handler = new IconsHandler($this->currentSetting, $this->iconsStyleFetcher, $this->serializer);
        $this->invokeMethod($handler, 'parseRequestBody', $request);
    }
}
