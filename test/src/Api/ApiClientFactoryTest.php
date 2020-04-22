<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Api;

use BluePsyduck\TestHelper\ReflectionTrait;
use Doctrine\ORM\EntityManagerInterface;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
use FactorioItemBrowser\PortalApi\Server\Api\Data;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use ReflectionException;

/**
 * The PHPUnit test of the ApiClientFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory
 */
class ApiClientFactoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked entity manager.
     * @var EntityManagerInterface&MockObject
     */
    protected $entityManager;

    /**
     * The mocked service manager.
     * @var ServiceManager&MockObject
     */
    protected $serviceManager;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->serviceManager = $this->createMock(ServiceManager::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $factory = new ApiClientFactory($this->entityManager, $this->serviceManager);

        $this->assertSame($this->entityManager, $this->extractProperty($factory, 'entityManager'));
        $this->assertSame($this->serviceManager, $this->extractProperty($factory, 'serviceManager'));
    }

    /**
     * Tests the create method.
     * @covers ::create
     */
    public function testCreate(): void
    {
        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);

        /* @var ApiClientFactory&MockObject $factory */
        $factory = $this->getMockBuilder(ApiClientFactory::class)
                        ->onlyMethods(['createApiClient'])
                        ->setConstructorArgs([$this->entityManager, $this->serviceManager])
                        ->getMock();
        $factory->expects($this->once())
                ->method('createApiClient')
                ->with($this->identicalTo($setting), $this->isTrue())
                ->willReturn($apiClient);

        $result = $factory->create($setting);

        $this->assertSame($apiClient, $result);
    }

    /**
     * Tests the createWithoutFallback method.
     * @covers ::createWithoutFallback
     */
    public function testCreateWithoutFallback(): void
    {
        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);

        /* @var ApiClientFactory&MockObject $factory */
        $factory = $this->getMockBuilder(ApiClientFactory::class)
                        ->onlyMethods(['createApiClient'])
                        ->setConstructorArgs([$this->entityManager, $this->serviceManager])
                        ->getMock();
        $factory->expects($this->once())
                ->method('createApiClient')
                ->with($this->identicalTo($setting), $this->isFalse())
                ->willReturn($apiClient);

        $result = $factory->createWithoutFallback($setting);

        $this->assertSame($apiClient, $result);
    }

    /**
     * Tests the createApiClient method.
     * @throws ReflectionException
     * @covers ::createApiClient
     */
    public function testCreateApiClient(): void
    {
        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);

        /* @var Data&MockObject $data */
        $data = $this->createMock(Data::class);
        $data->expects($this->once())
             ->method('getApiClient')
             ->willReturn($apiClient);

        $this->serviceManager->expects($this->never())
                             ->method('build');

        /* @var ApiClientFactory&MockObject $factory */
        $factory = $this->getMockBuilder(ApiClientFactory::class)
                        ->onlyMethods(['getData', 'configureApiClient'])
                        ->setConstructorArgs([$this->entityManager, $this->serviceManager])
                        ->getMock();
        $factory->expects($this->once())
                ->method('getData')
                ->with($this->identicalTo($setting), $this->isTrue())
                ->willReturn($data);
        $factory->expects($this->once())
                ->method('configureApiClient')
                ->with($this->identicalTo($apiClient), $this->identicalTo($setting), $this->isTrue());

        $result = $this->invokeMethod($factory, 'createApiClient', $setting, true);

        $this->assertSame($apiClient, $result);
    }

    /**
     * Tests the createApiClient method.
     * @throws ReflectionException
     * @covers ::createApiClient
     */
    public function testCreateApiClientWithoutClient(): void
    {
        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);

        /* @var Data&MockObject $data */
        $data = $this->createMock(Data::class);
        $data->expects($this->once())
             ->method('getApiClient')
             ->willReturn(null);

        $this->serviceManager->expects($this->once())
                             ->method('build')
                             ->with($this->identicalTo(ApiClientInterface::class))
                             ->willReturn($apiClient);

        /* @var ApiClientFactory&MockObject $factory */
        $factory = $this->getMockBuilder(ApiClientFactory::class)
                        ->onlyMethods(['getData', 'configureApiClient'])
                        ->setConstructorArgs([$this->entityManager, $this->serviceManager])
                        ->getMock();
        $factory->expects($this->once())
                ->method('getData')
                ->with($this->identicalTo($setting), $this->isTrue())
                ->willReturn($data);
        $factory->expects($this->once())
                ->method('configureApiClient')
                ->with($this->identicalTo($apiClient), $this->identicalTo($setting), $this->isTrue());

        $result = $this->invokeMethod($factory, 'createApiClient', $setting, true);

        $this->assertSame($apiClient, $result);
    }

    /**
     * Tests the configure method.
     * @covers ::configure
     */
    public function testConfigure(): void
    {
        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);

        /* @var ApiClientFactory&MockObject $factory */
        $factory = $this->getMockBuilder(ApiClientFactory::class)
                        ->onlyMethods(['configureApiClient'])
                        ->setConstructorArgs([$this->entityManager, $this->serviceManager])
                        ->getMock();
        $factory->expects($this->once())
                ->method('configureApiClient')
                ->with($this->identicalTo($apiClient), $this->identicalTo($setting), $this->isTrue());

        $factory->configure($apiClient, $setting);
    }

    /**
     * Tests the configureWithoutFallback method.
     * @covers ::configureWithoutFallback
     */
    public function testConfigureWithoutFallback(): void
    {
        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);

        /* @var ApiClientFactory&MockObject $factory */
        $factory = $this->getMockBuilder(ApiClientFactory::class)
                        ->onlyMethods(['configureApiClient'])
                        ->setConstructorArgs([$this->entityManager, $this->serviceManager])
                        ->getMock();
        $factory->expects($this->once())
                ->method('configureApiClient')
                ->with($this->identicalTo($apiClient), $this->identicalTo($setting), $this->isFalse());

        $factory->configureWithoutFallback($apiClient, $setting);
    }

    /**
     * Provides the data for the configureApiClient test.
     * @return array<mixed>
     */
    public function provideConfigureApiClient(): array
    {
        $modNames = ['foo', 'bar'];

        return [
            // Data is available, no reason to fallback to Vanilla.
            [true, true, $modNames, $modNames, true, false],
            [false, true, $modNames, $modNames, true, false],
            // Data is not available, so fallback to Vanilla. Do use authorization token.
            [true, false, $modNames, ['base'], true, true],
            // Data is not available, and we do not want to fallback. We are not allowed to use the token here.
            [false, false, $modNames, $modNames, false, false],
        ];
    }

    /**
     * Tests the configureApiClient method.
     * @param bool $withFallback
     * @param bool $hasData
     * @param array<string> $combinationModNames
     * @param array<string> $expectedModNames
     * @param bool $expectAuthorizationToken
     * @param bool $expectedIsFallback
     * @throws ReflectionException
     * @covers ::configureApiClient
     * @dataProvider provideConfigureApiClient
     */
    public function testConfigureApiClient(
        bool $withFallback,
        bool $hasData,
        array $combinationModNames,
        array $expectedModNames,
        bool $expectAuthorizationToken,
        bool $expectedIsFallback
    ): void {
        $locale = 'abc';
        $authorizationToken = 'def';

        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);
        $combination->expects($this->any())
                    ->method('getModNames')
                    ->willReturn($combinationModNames);

        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        $setting->expects($this->any())
                ->method('getHasData')
                ->willReturn($hasData);
        $setting->expects($this->any())
                ->method('getLocale')
                ->willReturn($locale);
        $setting->expects($this->any())
                ->method('getCombination')
                ->willReturn($combination);
        $setting->expects($this->any())
                ->method('getApiAuthorizationToken')
                ->willReturn($authorizationToken);

        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
                  ->method('setLocale')
                  ->with($this->identicalTo($locale));
        $apiClient->expects($this->once())
                  ->method('setModNames')
                  ->with($this->identicalTo($expectedModNames));
        $apiClient->expects($expectAuthorizationToken ? $this->once() : $this->never())
                  ->method('setAuthorizationToken')
                  ->with($this->identicalTo($authorizationToken));

        /* @var Data&MockObject $data */
        $data = $this->createMock(Data::class);
        $data->expects($this->once())
             ->method('setApiClient')
             ->with($this->identicalTo($apiClient))
             ->willReturnSelf();
        $data->expects($this->once())
             ->method('setIsFallback')
             ->with($this->identicalTo($expectedIsFallback))
             ->willReturnSelf();

        /* @var ApiClientFactory&MockObject $factory */
        $factory = $this->getMockBuilder(ApiClientFactory::class)
                        ->onlyMethods(['getData'])
                        ->setConstructorArgs([$this->entityManager, $this->serviceManager])
                        ->getMock();
        $factory->expects($this->once())
                ->method('getData')
                ->with($this->identicalTo($setting), $this->identicalTo($withFallback))
                ->willReturn($data);

        $this->invokeMethod($factory, 'configureApiClient', $apiClient, $setting, $withFallback);
    }

    /**
     * Tests the getData method.
     * @throws ReflectionException
     * @covers ::getData
     */
    public function testGetData(): void
    {
        $withFallback = true;
        $settingId = '0fe93664-3c62-4297-bf6a-55501e8770e3';

        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        $setting->expects($this->once())
                ->method('getId')
                ->willReturn(Uuid::fromString($settingId));

        $expectedData = new Data();
        $expectedData->setSetting($setting);

        $expectedDataArray = [
            $settingId => [
                1 => $expectedData,
            ],
        ];

        $factory = new ApiClientFactory($this->entityManager, $this->serviceManager);

        $result = $this->invokeMethod($factory, 'getData', $setting, $withFallback);

        $this->assertEquals($expectedData, $result);
        $this->assertEquals($expectedDataArray, $this->extractProperty($factory, 'data'));
    }

    /**
     * Tests the getData method.
     * @throws ReflectionException
     * @covers ::getData
     */
    public function testGetDataWithMatch(): void
    {
        $withFallback = true;
        $settingId = '0fe93664-3c62-4297-bf6a-55501e8770e3';

        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        $setting->expects($this->once())
                ->method('getId')
                ->willReturn(Uuid::fromString($settingId));

        /* @var Data&MockObject $data */
        $data = $this->createMock(Data::class);

        $dataArray = [
            $settingId => [
                1 => $data,
            ],
        ];

        $factory = new ApiClientFactory($this->entityManager, $this->serviceManager);
        $this->injectProperty($factory, 'data', $dataArray);

        $result = $this->invokeMethod($factory, 'getData', $setting, $withFallback);

        $this->assertSame($data, $result);
    }

    /**
     * Tests the createForModNames method.
     * @covers ::createForModNames
     */
    public function testCreateForModNames(): void
    {
        $modNames = ['abc', 'def'];

        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
                  ->method('setModNames')
                  ->with($this->identicalTo($modNames));

        $this->serviceManager->expects($this->once())
                             ->method('build')
                             ->with($this->identicalTo(ApiClientInterface::class))
                             ->willReturn($apiClient);

        $factory = new ApiClientFactory($this->entityManager, $this->serviceManager);
        $result = $factory->createForModNames($modNames);

        $this->assertSame($apiClient, $result);
    }

    /**
     * Provides the data for the persistAuthorizationTokens test.
     * @return array<mixed>
     */
    public function providePersistAuthorizationTokens(): array
    {
        return [
            [true, false, 'abc', 'abc', false, false],
            [true, false, 'abc', 'def', true, true],
            [true, true, 'abc', 'def', false, false],

            [false, true, 'abc', 'abc', false, false],
            [false, true, 'abc', 'def', true, true],
            [false, false, 'abc', 'def', false, false],
        ];
    }

    /**
     * Tests the persistAuthorizationTokens method.
     * @param bool $hasData
     * @param bool $isFallback
     * @param string $settingToken
     * @param string $clientToken
     * @param bool $expectSetToken
     * @param bool $expectPersist
     * @throws ReflectionException
     * @covers ::persistAuthorizationTokens
     * @dataProvider providePersistAuthorizationTokens
     */
    public function testPersistAuthorizationTokens(
        bool $hasData,
        bool $isFallback,
        string $settingToken,
        string $clientToken,
        bool $expectSetToken,
        bool $expectPersist
    ): void {
        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        $setting->expects($this->any())
                ->method('getHasData')
                ->willReturn($hasData);
        $setting->expects($this->any())
                ->method('getApiAuthorizationToken')
                ->willReturn($settingToken);
        $setting->expects($expectSetToken ? $this->once() : $this->never())
                ->method('setApiAuthorizationToken')
                ->with($this->identicalTo($clientToken));

        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->any())
                  ->method('getAuthorizationToken')
                  ->willReturn($clientToken);

        /* @var Data&MockObject $data */
        $data = $this->createMock(Data::class);
        $data->expects($this->any())
             ->method('getIsFallback')
             ->willReturn($isFallback);
        $data->expects($this->any())
             ->method('getSetting')
             ->willReturn($setting);
        $data->expects($this->any())
             ->method('getApiClient')
             ->willReturn($apiClient);

        $dataArray = [
            '0fe93664-3c62-4297-bf6a-55501e8770e3' => [
                1 => $data,
            ],
        ];

        $this->entityManager->expects($expectPersist ? $this->once() : $this->never())
                            ->method('persist')
                            ->with($this->identicalTo($setting));

        $factory = new ApiClientFactory($this->entityManager, $this->serviceManager);
        $this->injectProperty($factory, 'data', $dataArray);

        $this->invokeMethod($factory, 'persistAuthorizationTokens');
    }
}
