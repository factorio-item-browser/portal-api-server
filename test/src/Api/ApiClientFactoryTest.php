<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Api;

use BluePsyduck\TestHelper\ReflectionTrait;
use Doctrine\ORM\EntityManagerInterface;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
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
        $settingId = '0fe93664-3c62-4297-bf6a-55501e8770e3';

        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);

        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        $setting->expects($this->once())
                ->method('getId')
                ->willReturn(Uuid::fromString($settingId));

        $this->serviceManager->expects($this->once())
                             ->method('build')
                             ->with($this->identicalTo(ApiClientInterface::class))
                             ->willReturn($apiClient);

        /* @var ApiClientFactory&MockObject $factory */
        $factory = $this->getMockBuilder(ApiClientFactory::class)
                        ->onlyMethods(['configure'])
                        ->setConstructorArgs([$this->entityManager, $this->serviceManager])
                        ->getMock();
        $factory->expects($this->once())
                ->method('configure')
                ->with($this->identicalTo($apiClient), $this->identicalTo($setting));

        $result = $factory->create($setting);

        $this->assertSame($apiClient, $result);
    }

    /**
     * Tests the create method.
     * @throws ReflectionException
     * @covers ::create
     */
    public function testCreateWithExistingClient(): void
    {
        $settingId = '0fe93664-3c62-4297-bf6a-55501e8770e3';

        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);

        $apiClients = [
            $settingId => $apiClient,
        ];

        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        $setting->expects($this->once())
                ->method('getId')
                ->willReturn(Uuid::fromString($settingId));

        $this->serviceManager->expects($this->never())
                             ->method('build');

        /* @var ApiClientFactory&MockObject $factory */
        $factory = $this->getMockBuilder(ApiClientFactory::class)
                        ->onlyMethods(['configure'])
                        ->setConstructorArgs([$this->entityManager, $this->serviceManager])
                        ->getMock();
        $factory->expects($this->once())
                ->method('configure')
                ->with($this->identicalTo($apiClient));
        $this->injectProperty($factory, 'apiClients', $apiClients);

        $result = $factory->create($setting);

        $this->assertSame($apiClient, $result);
    }

    /**
     * Tests the configure method.
     * @throws ReflectionException
     * @covers ::configure
     */
    public function testConfigure(): void
    {
        $settingId = '0fe93664-3c62-4297-bf6a-55501e8770e3';
        $locale = 'abc';
        $modNames = ['def', 'ghi'];
        $authorizationToken = 'jkl';

        /* @var Setting&MockObject $setting1 */
        $setting1 = $this->createMock(Setting::class);
        /* @var ApiClientInterface&MockObject $apiClient1 */
        $apiClient1 = $this->createMock(ApiClientInterface::class);
        /* @var Setting&MockObject $setting2 */
        $setting2 = $this->createMock(Setting::class);
        /* @var ApiClientInterface&MockObject $apiClient2 */
        $apiClient2 = $this->createMock(ApiClientInterface::class);

        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);
        $combination->expects($this->once())
                    ->method('getModNames')
                    ->willReturn($modNames);

        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        $setting->expects($this->once())
                ->method('getId')
                ->willReturn(Uuid::fromString('0fe93664-3c62-4297-bf6a-55501e8770e3'));
        $setting->expects($this->once())
                ->method('getLocale')
                ->willReturn($locale);
        $setting->expects($this->once())
                ->method('getCombination')
                ->willReturn($combination);
        $setting->expects($this->once())
                ->method('getApiAuthorizationToken')
                ->willReturn($authorizationToken);

        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        $apiClient->expects($this->once())
                  ->method('setLocale')
                  ->with($this->identicalTo($locale))
                  ->willReturnSelf();
        $apiClient->expects($this->once())
                  ->method('setModNames')
                  ->with($this->identicalTo($modNames))
                  ->willReturnSelf();
        $apiClient->expects($this->once())
                  ->method('setAuthorizationToken')
                  ->with($this->identicalTo($authorizationToken))
                  ->willReturnSelf();

        $apiClients = [
            'foo' => $apiClient1,
            'bar' => $apiClient2,
        ];
        $expectedApiClients = [
            'foo' => $apiClient1,
            'bar' => $apiClient2,
            $settingId => $apiClient,
        ];
        $settings = [
            'foo' => $setting1,
            'bar' => $setting2,
        ];
        $expectedSettings = [
            'foo' => $setting1,
            'bar' => $setting2,
            $settingId => $setting,
        ];
        
        $factory = new ApiClientFactory($this->entityManager, $this->serviceManager);
        $this->injectProperty($factory, 'apiClients', $apiClients);
        $this->injectProperty($factory, 'settings', $settings);

        $factory->configure($apiClient, $setting);

        $this->assertEquals($expectedApiClients, $this->extractProperty($factory, 'apiClients'));
        $this->assertEquals($expectedSettings, $this->extractProperty($factory, 'settings'));
    }

    /**
     * Tests the persistAuthorizationTokens method.
     * @throws ReflectionException
     * @covers ::persistAuthorizationTokens
     */
    public function testPersistAuthorizationTokens(): void
    {
        $settingId1 = '0fe93664-3c62-4297-bf6a-55501e8770e3';
        $settingId2 = 'b7fdb8cb-5522-486b-b224-279f647b68f4';

        /* @var ApiClientInterface&MockObject $apiClient1 */
        $apiClient1 = $this->createMock(ApiClientInterface::class);
        $apiClient1->expects($this->once())
                   ->method('getAuthorizationToken')
                   ->willReturn('abc');

        /* @var Setting&MockObject $setting1 */
        $setting1 = $this->createMock(Setting::class);
        $setting1->expects($this->once())
                 ->method('getApiAuthorizationToken')
                 ->willReturn('def');
        $setting1->expects($this->once())
                 ->method('setApiAuthorizationToken')
                 ->with($this->identicalTo('abc'));

        /* @var ApiClientInterface&MockObject $apiClient2 */
        $apiClient2 = $this->createMock(ApiClientInterface::class);
        $apiClient2->expects($this->once())
                   ->method('getAuthorizationToken')
                   ->willReturn('ghi');

        /* @var Setting&MockObject $setting2 */
        $setting2 = $this->createMock(Setting::class);
        $setting2->expects($this->once())
                 ->method('getApiAuthorizationToken')
                 ->willReturn('ghi');
        $setting2->expects($this->never())
                 ->method('setApiAuthorizationToken');

        $apiClients = [
            $settingId1 => $apiClient1,
            $settingId2 => $apiClient2,
        ];
        $settings = [
            $settingId1 => $setting1,
            $settingId2 => $setting2,
        ];

        $this->entityManager->expects($this->once())
                            ->method('persist')
                            ->with($this->identicalTo($setting1));

        $factory = new ApiClientFactory($this->entityManager, $this->serviceManager);
        $this->injectProperty($factory, 'apiClients', $apiClients);
        $this->injectProperty($factory, 'settings', $settings);

        $factory->persistAuthorizationTokens();
    }
}
