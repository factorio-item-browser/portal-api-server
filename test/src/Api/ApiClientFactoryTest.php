<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Api;

use BluePsyduck\TestHelper\ReflectionTrait;
use Doctrine\ORM\EntityManagerInterface;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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
     * Tests the configure method.
     * @throws ReflectionException
     * @covers ::configure
     */
    public function testConfigure(): void
    {
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

        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        $setting->expects($this->once())
                ->method('getLocale')
                ->willReturn($locale);
        $setting->expects($this->once())
                ->method('getModNames')
                ->willReturn($modNames);
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

        $clientsAndSettings = [
            [$apiClient1, $setting1],
            [$apiClient2, $setting2],
        ];
        $expectedClientsAndSettings = [
            [$apiClient1, $setting1],
            [$apiClient2, $setting2],
            [$apiClient, $setting],
        ];

        $factory = new ApiClientFactory($this->entityManager, $this->serviceManager);
        $this->injectProperty($factory, 'clientsAndSettings', $clientsAndSettings);

        $factory->configure($apiClient, $setting);

        $this->assertEquals($expectedClientsAndSettings, $this->extractProperty($factory, 'clientsAndSettings'));
    }

    /**
     * Tests the persistAuthorizationTokens method.
     * @throws ReflectionException
     * @covers ::persistAuthorizationTokens
     */
    public function testPersistAuthorizationTokens(): void
    {
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

        $this->entityManager->expects($this->once())
                            ->method('persist')
                            ->with($this->identicalTo($setting1));

        $clientsAndSettings = [
            [$apiClient1, $setting1],
            [$apiClient2, $setting2],
        ];

        $factory = new ApiClientFactory($this->entityManager, $this->serviceManager);
        $this->injectProperty($factory, 'clientsAndSettings', $clientsAndSettings);

        $factory->persistAuthorizationTokens();
    }
}
