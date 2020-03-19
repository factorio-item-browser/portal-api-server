<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Helper;

use BluePsyduck\TestHelper\ReflectionTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\Entity;
use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericDetailsResponse;
use FactorioItemBrowser\PortalApi\Server\Api\ApiClientFactory;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the SidebarEntitiesHelper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper
 */
class SidebarEntitiesHelperTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked api client factory.
     * @var ApiClientFactory&MockObject
     */
    protected $apiClientFactory;

    /**
     * The mocked entity manager.
     * @var EntityManagerInterface&MockObject
     */
    protected $entityManager;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClientFactory = $this->createMock(ApiClientFactory::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $helper = new SidebarEntitiesHelper($this->apiClientFactory, $this->entityManager);

        $this->assertSame($this->apiClientFactory, $this->extractProperty($helper, 'apiClientFactory'));
        $this->assertSame($this->entityManager, $this->extractProperty($helper, 'entityManager'));
    }

    /**
     * Tests the createAssociativeMap method.
     * @throws ReflectionException
     * @covers ::createAssociativeMap
     */
    public function testCreateAssociativeMap(): void
    {
        $entity1 = new SidebarEntity();
        $entity1->setType('abc')
                ->setName('def');

        $entity2 = new SidebarEntity();
        $entity2->setType('abc')
                ->setName('ghi');

        $entity3 = new SidebarEntity();
        $entity3->setType('jkl')
                ->setName('mno');

        $entities = [$entity1, $entity2, $entity3];
        $expectedResult = [
            'abc|def' => $entity1,
            'abc|ghi' => $entity2,
            'jkl|mno' => $entity3,
        ];

        $helper = new SidebarEntitiesHelper($this->apiClientFactory, $this->entityManager);
        $result = $this->invokeMethod($helper, 'createAssociativeMap', $entities);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the replaceEntities method.
     * @covers ::replaceEntities
     */
    public function testReplaceEntities(): void
    {
        /* @var DateTime&MockObject $lastViewTime */
        $lastViewTime = $this->createMock(DateTime::class);

        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);

        $sidebarEntities = [
            $this->createMock(SidebarEntity::class),
            $this->createMock(SidebarEntity::class),
        ];

        $newEntities = [
            $this->createMock(SidebarEntity::class),
            $this->createMock(SidebarEntity::class),
        ];
        
        /* @var SidebarEntity&MockObject $oldEntity1 */
        $oldEntity1 = $this->createMock(SidebarEntity::class);
        $oldEntity1->expects($this->once())
                   ->method('setLabel')
                   ->with($this->identicalTo('foo'))
                   ->willReturnSelf();
        $oldEntity1->expects($this->once())
                   ->method('setLastViewTime')
                   ->with($this->identicalTo($lastViewTime))
                   ->willReturnSelf();
        $oldEntity1->expects($this->once())
                   ->method('setPinnedPosition')
                   ->with($this->identicalTo(42))
                   ->willReturnSelf();

        /* @var SidebarEntity&MockObject $oldEntity2 */
        $oldEntity2 = $this->createMock(SidebarEntity::class);

        /* @var SidebarEntity&MockObject $newEntity1 */
        $newEntity1 = $this->createMock(SidebarEntity::class);
        $newEntity1->expects($this->once())
                   ->method('getLabel')
                   ->willReturn('foo');
        $newEntity1->expects($this->once())
                   ->method('getLastViewTime')
                   ->willReturn($lastViewTime);
        $newEntity1->expects($this->once())
                   ->method('getPinnedPosition')
                   ->willReturn(42);

        /* @var SidebarEntity&MockObject $newEntity2 */
        $newEntity2 = $this->createMock(SidebarEntity::class);
        $newEntity2->expects($this->once())
                   ->method('setSetting')
                   ->with($this->identicalTo($setting));

        $mappedOldEntities = [
            'abc|def' => $oldEntity1,
            'ghi|jkl' => $oldEntity2,
        ];
        $mappedNewEntities = [
            'abc|def' => $newEntity1,
            'mno|pqr' => $newEntity2,
        ];

        /* @var Collection&MockObject $sidebarEntityCollection */
        $sidebarEntityCollection = $this->createMock(Collection::class);
        $sidebarEntityCollection->expects($this->once())
                                ->method('toArray')
                                ->willReturn($sidebarEntities);
        $sidebarEntityCollection->expects($this->once())
                                ->method('removeElement')
                                ->with($oldEntity2);
        $sidebarEntityCollection->expects($this->once())
                                ->method('add')
                                ->with($newEntity2);

        $setting->expects($this->any())
                ->method('getSidebarEntities')
                ->willReturn($sidebarEntityCollection);

        $this->entityManager->expects($this->exactly(2))
                            ->method('persist')
                            ->withConsecutive(
                                [$this->identicalTo($oldEntity1)],
                                [$this->identicalTo($newEntity2)]
                            );
        $this->entityManager->expects($this->once())
                            ->method('remove')
                            ->with($this->identicalTo($oldEntity2));

        /* @var SidebarEntitiesHelper&MockObject $helper */
        $helper = $this->getMockBuilder(SidebarEntitiesHelper::class)
                       ->onlyMethods(['createAssociativeMap'])
                       ->setConstructorArgs([$this->apiClientFactory, $this->entityManager])
                       ->getMock();
        $helper->expects($this->exactly(2))
               ->method('createAssociativeMap')
               ->withConsecutive(
                   [$this->identicalTo($sidebarEntities)],
                   [$this->identicalTo($newEntities)]
               )
               ->willReturnOnConsecutiveCalls(
                   $mappedOldEntities,
                   $mappedNewEntities
               );

        $helper->replaceEntities($setting, $newEntities);
    }

    /**
     * Tests the refreshLabels method.
     * @throws PortalApiServerException
     * @covers ::refreshLabels
     */
    public function testRefreshLabels(): void
    {
        $entities = [
            $this->createMock(SidebarEntity::class),
            $this->createMock(SidebarEntity::class),
        ];
        $mappedEntities = [
            'abc' => $this->createMock(SidebarEntity::class),
            'def' => $this->createMock(SidebarEntity::class),
        ];

        /* @var GenericDetailsRequest&MockObject $request */
        $request = $this->createMock(GenericDetailsRequest::class);
        /* @var GenericDetailsResponse&MockObject $response */
        $response = $this->createMock(GenericDetailsResponse::class);

        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        $setting->expects($this->once())
                ->method('getSidebarEntities')
                ->willReturn(new ArrayCollection($entities));

        /* @var ApiClientInterface&MockObject $client */
        $client = $this->createMock(ApiClientInterface::class);
        $client->expects($this->once())
               ->method('fetchResponse')
               ->with($this->identicalTo($request))
               ->willReturn($response);

        $this->apiClientFactory->expects($this->once())
                               ->method('create')
                               ->with($this->identicalTo($setting))
                               ->willReturn($client);

        /* @var SidebarEntitiesHelper&MockObject $helper */
        $helper = $this->getMockBuilder(SidebarEntitiesHelper::class)
                       ->onlyMethods(['createAssociativeMap', 'createDetailsRequest', 'processDetailsResponse'])
                       ->setConstructorArgs([$this->apiClientFactory, $this->entityManager])
                       ->getMock();
        $helper->expects($this->once())
               ->method('createAssociativeMap')
               ->with($this->identicalTo($entities))
               ->willReturn($mappedEntities);
        $helper->expects($this->once())
               ->method('createDetailsRequest')
               ->with($this->identicalTo($mappedEntities))
               ->willReturn($request);
        $helper->expects($this->once())
               ->method('processDetailsResponse')
               ->with($this->identicalTo($response), $this->identicalTo($mappedEntities));

        $helper->refreshLabels($setting);
    }

    /**
     * Tests the refreshLabels method.
     * @throws PortalApiServerException
     * @covers ::refreshLabels
     */
    public function testRefreshLabelsWithException(): void
    {
        $entities = [
            $this->createMock(SidebarEntity::class),
            $this->createMock(SidebarEntity::class),
        ];
        $mappedEntities = [
            'abc' => $this->createMock(SidebarEntity::class),
            'def' => $this->createMock(SidebarEntity::class),
        ];

        /* @var GenericDetailsRequest&MockObject $request */
        $request = $this->createMock(GenericDetailsRequest::class);

        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        $setting->expects($this->once())
                ->method('getSidebarEntities')
                ->willReturn(new ArrayCollection($entities));

        /* @var ApiClientInterface&MockObject $client */
        $client = $this->createMock(ApiClientInterface::class);
        $client->expects($this->once())
               ->method('fetchResponse')
               ->with($this->identicalTo($request))
               ->willThrowException($this->createMock(ApiClientException::class));

        $this->apiClientFactory->expects($this->once())
                               ->method('create')
                               ->with($this->identicalTo($setting))
                               ->willReturn($client);

        $this->expectException(FailedApiRequestException::class);

        /* @var SidebarEntitiesHelper&MockObject $helper */
        $helper = $this->getMockBuilder(SidebarEntitiesHelper::class)
                       ->onlyMethods(['createAssociativeMap', 'createDetailsRequest', 'processDetailsResponse'])
                       ->setConstructorArgs([$this->apiClientFactory, $this->entityManager])
                       ->getMock();
        $helper->expects($this->once())
               ->method('createAssociativeMap')
               ->with($this->identicalTo($entities))
               ->willReturn($mappedEntities);
        $helper->expects($this->once())
               ->method('createDetailsRequest')
               ->with($this->identicalTo($mappedEntities))
               ->willReturn($request);
        $helper->expects($this->never())
               ->method('processDetailsResponse');

        $helper->refreshLabels($setting);
    }

    /**
     * Tests the createDetailsRequest method.
     * @throws ReflectionException
     * @covers ::createDetailsRequest
     */
    public function testCreateDetailsRequest(): void
    {
        $entity1 = new SidebarEntity();
        $entity1->setType('abc')
                ->setName('def');

        $entity2 = new SidebarEntity();
        $entity2->setType('abc')
                ->setName('ghi');

        $entity3 = new SidebarEntity();
        $entity3->setType('jkl')
                ->setName('mno');

        $entities = [$entity1, $entity2, $entity3];

        $requestEntity1 = new Entity();
        $requestEntity1->setType('abc')
                       ->setName('def');

        $requestEntity2 = new Entity();
        $requestEntity2->setType('abc')
                       ->setName('ghi');

        $requestEntity3 = new Entity();
        $requestEntity3->setType('jkl')
                       ->setName('mno');

        $expectedResult = new GenericDetailsRequest();
        $expectedResult->setEntities([$requestEntity1, $requestEntity2, $requestEntity3]);

        $helper = new SidebarEntitiesHelper($this->apiClientFactory, $this->entityManager);
        $result = $this->invokeMethod($helper, 'createDetailsRequest', $entities);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the processDetailsResponse method.
     * @throws ReflectionException
     * @covers ::processDetailsResponse
     */
    public function testProcessDetailsResponse(): void
    {
        $responseEntity1 = new GenericEntity();
        $responseEntity1->setType('abc')
                        ->setName('def')
                        ->setLabel('ghi');

        $responseEntity2 = new GenericEntity();
        $responseEntity2->setType('jkl')
                        ->setName('mno')
                        ->setLabel('pqr');

        $responseEntity3 = new GenericEntity();
        $responseEntity3->setType('stu')
                        ->setName('vwx')
                        ->setLabel('yza');

        $response = new GenericDetailsResponse();
        $response->setEntities([$responseEntity1, $responseEntity2, $responseEntity3]);

        /* @var SidebarEntity&MockObject $entity1 */
        $entity1 = $this->createMock(SidebarEntity::class);
        $entity1->expects($this->once())
                ->method('getLabel')
                ->willReturn('foo');
        $entity1->expects($this->once())
                ->method('setLabel')
                ->with($this->identicalTo('ghi'));

        /* @var SidebarEntity&MockObject $entity2 */
        $entity2 = $this->createMock(SidebarEntity::class);
        $entity2->expects($this->once())
                ->method('getLabel')
                ->willReturn('pqr');
        $entity2->expects($this->never())
                ->method('setLabel');

        $mappedEntities = [
            'abc|def' => $entity1,
            'jkl|mno' => $entity2,
        ];

        $helper = new SidebarEntitiesHelper($this->apiClientFactory, $this->entityManager);
        $this->invokeMethod($helper, 'processDetailsResponse', $response, $mappedEntities);
    }
}
