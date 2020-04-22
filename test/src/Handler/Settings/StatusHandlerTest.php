<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Settings;

use BluePsyduck\TestHelper\ReflectionTrait;
use DateTime;
use Exception;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Combination\CombinationStatusRequest;
use FactorioItemBrowser\Api\Client\Response\Combination\CombinationStatusResponse;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\InvalidRequestException;
use FactorioItemBrowser\PortalApi\Server\Handler\Settings\StatusHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingStatusData;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use ReflectionException;

/**
 * The PHPUnit test of the StatusHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Settings\StatusHandler
 */
class StatusHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked api client factory.
     * @var ApiClientFactory&MockObject
     */
    protected $apiClientFactory;

    /**
     * The mocked combination helper.
     * @var CombinationHelper&MockObject
     */
    protected $combinationHelper;

    /**
     * The mocked current setting.
     * @var Setting&MockObject
     */
    protected $currentSetting;

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

        $this->apiClientFactory = $this->createMock(ApiClientFactory::class);
        $this->combinationHelper = $this->createMock(CombinationHelper::class);
        $this->currentSetting = $this->createMock(Setting::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new StatusHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentSetting,
            $this->serializer
        );

        $this->assertSame($this->apiClientFactory, $this->extractProperty($handler, 'apiClientFactory'));
        $this->assertSame($this->combinationHelper, $this->extractProperty($handler, 'combinationHelper'));
        $this->assertSame($this->currentSetting, $this->extractProperty($handler, 'currentSetting'));
        $this->assertSame($this->serializer, $this->extractProperty($handler, 'serializer'));
    }

    /**
     * Tests the handle method.
     * @throws Exception
     * @covers ::handle
     */
    public function testHandle(): void
    {
        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        /* @var CombinationStatusResponse&MockObject $combinationStatus */
        $combinationStatus = $this->createMock(CombinationStatusResponse::class);
        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);
        /* @var SettingStatusData&MockObject $settingStatus */
        $settingStatus = $this->createMock(SettingStatusData::class);

        $this->combinationHelper->expects($this->once())
                                ->method('createCombinationFromStatusResponse')
                                ->with($this->identicalTo($combinationStatus))
                                ->willReturn($combination);

        /* @var StatusHandler&MockObject $handler */
        $handler = $this->getMockBuilder(StatusHandler::class)
                        ->onlyMethods(['getApiClientForRequest', 'requestCombinationStatus', 'createSettingStatus'])
                        ->setConstructorArgs([
                            $this->apiClientFactory,
                            $this->combinationHelper,
                            $this->currentSetting,
                            $this->serializer,
                        ])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getApiClientForRequest')
                ->with($this->identicalTo($request))
                ->willReturn($apiClient);
        $handler->expects($this->once())
                ->method('requestCombinationStatus')
                ->with($this->identicalTo($apiClient))
                ->willReturn($combinationStatus);
        $handler->expects($this->once())
                ->method('createSettingStatus')
                ->with($this->identicalTo($combination))
                ->willReturn($settingStatus);

        /* @var TransferResponse $result */
        $result = $handler->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        $this->assertEquals($settingStatus, $result->getTransfer());
    }

    /**
     * Tests the getApiClientForRequest method.
     * @throws ReflectionException
     * @covers ::getApiClientForRequest
     */
    public function testGetApiClientForRequestWithPost(): void
    {
        $method = 'POST';
        $modNames = ['abc', 'def'];

        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getMethod')
                ->willReturn($method);

        $this->apiClientFactory->expects($this->never())
                               ->method('createWithoutFallback');
        $this->apiClientFactory->expects($this->once())
                               ->method('createForModNames')
                               ->with($this->identicalTo($modNames))
                               ->willReturn($apiClient);

        /* @var StatusHandler&MockObject $handler */
        $handler = $this->getMockBuilder(StatusHandler::class)
                        ->onlyMethods(['extractModNamesFromRequest'])
                        ->setConstructorArgs([
                            $this->apiClientFactory,
                            $this->combinationHelper,
                            $this->currentSetting,
                            $this->serializer,
                        ])
                        ->getMock();
        $handler->expects($this->once())
                ->method('extractModNamesFromRequest')
                ->with($this->identicalTo($request))
                ->willReturn($modNames);

        $result = $this->invokeMethod($handler, 'getApiClientForRequest', $request);

        $this->assertSame($apiClient, $result);
    }

    /**
     * Tests the getApiClientForRequest method.
     * @throws ReflectionException
     * @covers ::getApiClientForRequest
     */
    public function testGetApiClientForRequestWithGet(): void
    {
        $method = 'GET';

        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getMethod')
                ->willReturn($method);

        $this->apiClientFactory->expects($this->once())
                               ->method('createWithoutFallback')
                               ->with($this->identicalTo($this->currentSetting))
                               ->willReturn($apiClient);
        $this->apiClientFactory->expects($this->never())
                               ->method('createForModNames');

        /* @var StatusHandler&MockObject $handler */
        $handler = $this->getMockBuilder(StatusHandler::class)
                        ->onlyMethods(['extractModNamesFromRequest'])
                        ->setConstructorArgs([
                            $this->apiClientFactory,
                            $this->combinationHelper,
                            $this->currentSetting,
                            $this->serializer,
                        ])
                        ->getMock();
        $handler->expects($this->never())
                ->method('extractModNamesFromRequest');

        $result = $this->invokeMethod($handler, 'getApiClientForRequest', $request);

        $this->assertSame($apiClient, $result);
    }

    /**
     * Tests the extractModNamesFromRequest method.
     * @throws ReflectionException
     * @covers ::extractModNamesFromRequest
     */
    public function testExtractModNamesFromRequest(): void
    {
        $contents = 'abc';
        $modNames = ['def', 'ghi'];

        /* @var StreamInterface&MockObject $body */
        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
             ->method('getContents')
             ->willReturn($contents);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getBody')
                ->willReturn($body);

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($contents),
                             $this->identicalTo('array<string>'),
                             $this->identicalTo('json')
                         )
                         ->willReturn($modNames);

        $handler = new StatusHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentSetting,
            $this->serializer
        );

        $result = $this->invokeMethod($handler, 'extractModNamesFromRequest', $request);

        $this->assertSame($modNames, $result);
    }

    /**
     * Tests the extractModNamesFromRequest method.
     * @throws ReflectionException
     * @covers ::extractModNamesFromRequest
     */
    public function testExtractModNamesFromRequestWithException(): void
    {
        $contents = 'abc';

        /* @var StreamInterface&MockObject $body */
        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
             ->method('getContents')
             ->willReturn($contents);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getBody')
                ->willReturn($body);

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($contents),
                             $this->identicalTo('array<string>'),
                             $this->identicalTo('json')
                         )
                         ->willThrowException($this->createMock(Exception::class));

        $this->expectException(InvalidRequestException::class);

        $handler = new StatusHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentSetting,
            $this->serializer
        );

        $this->invokeMethod($handler, 'extractModNamesFromRequest', $request);
    }

    /**
     * Tests the extractModNamesFromRequest method.
     * @throws ReflectionException
     * @covers ::extractModNamesFromRequest
     */
    public function testExtractModNamesFromRequestWithoutModNames(): void
    {
        $contents = 'abc';
        $modNames = [];

        /* @var StreamInterface&MockObject $body */
        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
             ->method('getContents')
             ->willReturn($contents);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getBody')
                ->willReturn($body);

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($contents),
                             $this->identicalTo('array<string>'),
                             $this->identicalTo('json')
                         )
                         ->willReturn($modNames);

        $this->expectException(InvalidRequestException::class);

        $handler = new StatusHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentSetting,
            $this->serializer
        );

        $this->invokeMethod($handler, 'extractModNamesFromRequest', $request);
    }

    /**
     * Tests the requestCombinationStatus method.
     * @throws ReflectionException
     * @covers ::requestCombinationStatus
     */
    public function testRequestCombinationStatus(): void
    {
        $expectedRequest = new CombinationStatusRequest();

        /* @var CombinationStatusResponse&MockObject $response */
        $response = $this->createMock(CombinationStatusResponse::class);

        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
                  ->method('fetchResponse')
                  ->with($this->equalTo($expectedRequest))
                  ->willReturn($response);

        $handler = new StatusHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentSetting,
            $this->serializer
        );

        $result = $this->invokeMethod($handler, 'requestCombinationStatus', $apiClient);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the requestCombinationStatus method.
     * @throws ReflectionException
     * @covers ::requestCombinationStatus
     */
    public function testRequestCombinationStatusWithException(): void
    {
        $expectedRequest = new CombinationStatusRequest();

        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
                  ->method('fetchResponse')
                  ->with($this->equalTo($expectedRequest))
                  ->willThrowException($this->createMock(ApiClientException::class));

        $this->expectException(FailedApiRequestException::class);

        $handler = new StatusHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentSetting,
            $this->serializer
        );

        $this->invokeMethod($handler, 'requestCombinationStatus', $apiClient);
    }

    /**
     * Tests the createSettingStatus method.
     * @throws ReflectionException
     * @covers ::createSettingStatus
     */
    public function testCreateSettingStatus(): void
    {
        $status = 'abc';

        /* @var DateTime&MockObject $exportTime */
        $exportTime = $this->createMock(DateTime::class);

        $combination = new Combination();
        $combination->setStatus($status)
                    ->setExportTime($exportTime);

        $expectedResult = new SettingStatusData();
        $expectedResult->setStatus($status)
                       ->setExportTime($exportTime);

        $handler = new StatusHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentSetting,
            $this->serializer
        );

        $result = $this->invokeMethod($handler, 'createSettingStatus', $combination);

        $this->assertEquals($expectedResult, $result);
    }
}
