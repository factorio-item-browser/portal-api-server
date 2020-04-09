<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Settings;

use BluePsyduck\TestHelper\ReflectionTrait;
use Exception;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Combination\CombinationExportRequest;
use FactorioItemBrowser\Api\Client\Request\Combination\CombinationStatusRequest;
use FactorioItemBrowser\Api\Client\Response\Combination\CombinationStatusResponse;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
use FactorioItemBrowser\PortalApi\Server\Constant\CombinationStatus;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\InvalidRequestException;
use FactorioItemBrowser\PortalApi\Server\Handler\Settings\CreateHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
use FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingCreateData;
use JMS\Serializer\SerializerInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use ReflectionException;

/**
 * The PHPUnit test of the CreateHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Settings\CreateHandler
 */
class CreateHandlerTest extends TestCase
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
     * The mocked current user.
     * @var User&MockObject
     */
    protected $currentUser;

    /**
     * The mocked serializer.
     * @var SerializerInterface&MockObject
     */
    protected $serializer;

    /**
     * The mocked setting repository.
     * @var SettingRepository&MockObject
     */
    protected $settingRepository;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClientFactory = $this->createMock(ApiClientFactory::class);
        $this->combinationHelper = $this->createMock(CombinationHelper::class);
        $this->currentUser = $this->createMock(User::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->settingRepository = $this->createMock(SettingRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new CreateHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentUser,
            $this->serializer,
            $this->settingRepository
        );

        $this->assertSame($this->apiClientFactory, $this->extractProperty($handler, 'apiClientFactory'));
        $this->assertSame($this->combinationHelper, $this->extractProperty($handler, 'combinationHelper'));
        $this->assertSame($this->currentUser, $this->extractProperty($handler, 'currentUser'));
        $this->assertSame($this->serializer, $this->extractProperty($handler, 'serializer'));
        $this->assertSame($this->settingRepository, $this->extractProperty($handler, 'settingRepository'));
    }

    /**
     * Tests the handle method.
     * @throws Exception
     * @covers ::handle
     */
    public function testHandle(): void
    {
        $modNames = ['abc', 'def'];

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        /* @var CombinationStatusResponse&MockObject $combinationStatus */
        $combinationStatus = $this->createMock(CombinationStatusResponse::class);
        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);

        /* @var SettingCreateData&MockObject $settingData */
        $settingData = $this->createMock(SettingCreateData::class);
        $settingData->expects($this->once())
                    ->method('getModNames')
                    ->willReturn($modNames);

        $this->apiClientFactory->expects($this->once())
                               ->method('createForModNames')
                               ->with($this->identicalTo($modNames))
                               ->willReturn($apiClient);

        /* @var CreateHandler&MockObject $handler */
        $handler = $this->getMockBuilder(CreateHandler::class)
                        ->onlyMethods([
                            'parseRequestBody',
                            'fetchCombinationStatus',
                            'fetchCombination',
                            'triggerExport',
                            'createSetting',
                        ])
                        ->setConstructorArgs([
                            $this->apiClientFactory,
                            $this->combinationHelper,
                            $this->currentUser,
                            $this->serializer,
                            $this->settingRepository,
                        ])
                        ->getMock();
        $handler->expects($this->once())
                ->method('parseRequestBody')
                ->with($this->identicalTo($request))
                ->willReturn($settingData);
        $handler->expects($this->once())
                ->method('fetchCombinationStatus')
                ->with($this->identicalTo($apiClient))
                ->willReturn($combinationStatus);
        $handler->expects($this->once())
                ->method('fetchCombination')
                ->with($this->identicalTo($combinationStatus))
                ->willReturn($combination);
        $handler->expects($this->once())
                ->method('triggerExport')
                ->with($this->identicalTo($combination), $this->identicalTo($apiClient));
        $handler->expects($this->once())
                ->method('createSetting')
                ->with($this->identicalTo($combination), $this->identicalTo($settingData));

        $result = $handler->handle($request);
        $this->assertInstanceOf(EmptyResponse::class, $result);
    }

    /**
     * Tests the parseRequestBody method.
     * @throws ReflectionException
     * @covers ::parseRequestBody
     */
    public function testParseRequestBody(): void
    {
        $requestBody = 'abc';

        /* @var SettingCreateData&MockObject $settingData */
        $settingData = $this->createMock(SettingCreateData::class);

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
                             $this->identicalTo(SettingCreateData::class),
                             $this->identicalTo('json')
                         )
                         ->willReturn($settingData);

        $handler = new CreateHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentUser,
            $this->serializer,
            $this->settingRepository
        );
        $result = $this->invokeMethod($handler, 'parseRequestBody', $request);

        $this->assertSame($settingData, $result);
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
                             $this->identicalTo(SettingCreateData::class),
                             $this->identicalTo('json')
                         )
                         ->willThrowException($this->createMock(Exception::class));

        $this->expectException(InvalidRequestException::class);

        $handler = new CreateHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentUser,
            $this->serializer,
            $this->settingRepository
        );
        $this->invokeMethod($handler, 'parseRequestBody', $request);
    }

    /**
     * Tests the fetchCombinationStatus method.
     * @throws ReflectionException
     * @covers ::fetchCombinationStatus
     */
    public function testFetchCombinationStatus(): void
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

        $handler = new CreateHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentUser,
            $this->serializer,
            $this->settingRepository
        );
        $result = $this->invokeMethod($handler, 'fetchCombinationStatus', $apiClient);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the fetchCombinationStatus method.
     * @throws ReflectionException
     * @covers ::fetchCombinationStatus
     */
    public function testFetchCombinationStatusWithException(): void
    {
        $expectedRequest = new CombinationStatusRequest();

        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
                  ->method('fetchResponse')
                  ->with($this->equalTo($expectedRequest))
                  ->willThrowException($this->createMock(ApiClientException::class));

        $this->expectException(FailedApiRequestException::class);

        $handler = new CreateHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentUser,
            $this->serializer,
            $this->settingRepository
        );
        $this->invokeMethod($handler, 'fetchCombinationStatus', $apiClient);
    }

    /**
     * Tests the fetchCombination method.
     * @throws ReflectionException
     * @covers ::fetchCombination
     */
    public function testFetchCombination(): void
    {
        /* @var CombinationStatusResponse&MockObject $combinationStatus */
        $combinationStatus = $this->createMock(CombinationStatusResponse::class);
        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);

        $this->combinationHelper->expects($this->once())
                                ->method('createCombinationFromStatusResponse')
                                ->with($this->identicalTo($combinationStatus))
                                ->willReturn($combination);
        $this->combinationHelper->expects($this->once())
                                ->method('persist')
                                ->with($this->identicalTo($combination));

        $handler = new CreateHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentUser,
            $this->serializer,
            $this->settingRepository
        );
        $result = $this->invokeMethod($handler, 'fetchCombination', $combinationStatus);

        $this->assertSame($combination, $result);
    }

    /**
     * Tests the triggerExport method.
     * @throws ReflectionException
     * @covers ::triggerExport
     */
    public function testTriggerExport(): void
    {
        $status = CombinationStatus::UNKNOWN;
        $expectedRequest = new CombinationExportRequest();

        /* @var CombinationStatusResponse&MockObject $response */
        $response = $this->createMock(CombinationStatusResponse::class);

        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);
        $combination->expects($this->once())
                    ->method('getStatus')
                    ->willReturn($status);

        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
                  ->method('fetchResponse')
                  ->with($this->equalTo($expectedRequest))
                  ->willReturn($response);

        $this->combinationHelper->expects($this->once())
                                ->method('hydrateStatusResponseToCombination')
                                ->with($this->identicalTo($response), $this->identicalTo($combination));

        $handler = new CreateHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentUser,
            $this->serializer,
            $this->settingRepository
        );
        $this->invokeMethod($handler, 'triggerExport', $combination, $apiClient);
    }

    /**
     * Tests the triggerExport method.
     * @throws ReflectionException
     * @covers ::triggerExport
     */
    public function testTriggerExportWithException(): void
    {
        $status = CombinationStatus::UNKNOWN;
        $expectedRequest = new CombinationExportRequest();

        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);
        $combination->expects($this->once())
                    ->method('getStatus')
                    ->willReturn($status);

        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
                  ->method('fetchResponse')
                  ->with($this->equalTo($expectedRequest))
                  ->willThrowException($this->createMock(ApiClientException::class));

        $this->combinationHelper->expects($this->never())
                                ->method('hydrateStatusResponseToCombination');

        $this->expectException(FailedApiRequestException::class);

        $handler = new CreateHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentUser,
            $this->serializer,
            $this->settingRepository
        );
        $this->invokeMethod($handler, 'triggerExport', $combination, $apiClient);
    }

    /**
     * Tests the triggerExport method.
     * @throws ReflectionException
     * @covers ::triggerExport
     */
    public function testTriggerExportWithoutRequest(): void
    {
        $status = CombinationStatus::AVAILABLE;

        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);
        $combination->expects($this->once())
                    ->method('getStatus')
                    ->willReturn($status);

        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->never())
                  ->method('fetchResponse');

        $this->combinationHelper->expects($this->never())
                                ->method('hydrateStatusResponseToCombination');

        $handler = new CreateHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentUser,
            $this->serializer,
            $this->settingRepository
        );
        $this->invokeMethod($handler, 'triggerExport', $combination, $apiClient);
    }

    /**
     * Tests the createSetting method.
     * @throws ReflectionException
     * @covers ::createSetting
     */
    public function testCreateSetting(): void
    {
        $name = 'abc';
        $recipeMode = 'def';
        $locale = 'ghi';

        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);

        /* @var SettingCreateData&MockObject $settingData */
        $settingData = $this->createMock(SettingCreateData::class);
        $settingData->expects($this->once())
                    ->method('getName')
                    ->willReturn($name);
        $settingData->expects($this->once())
                    ->method('getRecipeMode')
                    ->willReturn($recipeMode);
        $settingData->expects($this->once())
                    ->method('getLocale')
                    ->willReturn($locale);

        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        $setting->expects($this->once())
                ->method('setName')
                ->with($this->identicalTo($name))
                ->willReturnSelf();
        $setting->expects($this->once())
                ->method('setRecipeMode')
                ->with($this->identicalTo($recipeMode))
                ->willReturnSelf();
        $setting->expects($this->once())
                ->method('setLocale')
                ->with($this->identicalTo($locale))
                ->willReturnSelf();

        $this->settingRepository->expects($this->once())
                                ->method('createSetting')
                                ->with($this->identicalTo($this->currentUser), $this->identicalTo($combination))
                                ->willReturn($setting);

        $handler = new CreateHandler(
            $this->apiClientFactory,
            $this->combinationHelper,
            $this->currentUser,
            $this->serializer,
            $this->settingRepository
        );
        $this->invokeMethod($handler, 'createSetting', $combination, $settingData);
    }
}
