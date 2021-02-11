<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Helper;

use BluePsyduck\MapperManager\MapperManagerInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use FactorioItemBrowser\Api\Client\ClientInterface;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericDetailsResponse;
use FactorioItemBrowser\Api\Client\Transfer\Entity;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the SidebarEntitiesHelper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper
 */
class SidebarEntitiesHelperTest extends TestCase
{
    /** @var ClientInterface&MockObject */
    private ClientInterface $apiClient;
    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $entityManager;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;

    protected function setUp(): void
    {
        $this->apiClient = $this->createMock(ClientInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return SidebarEntitiesHelper&MockObject
     */
    private function createInstance(array $mockedMethods = []): SidebarEntitiesHelper
    {
        return $this->getMockBuilder(SidebarEntitiesHelper::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->apiClient,
                        $this->entityManager,
                        $this->mapperManager,
                    ])
                    ->getMock();
    }

    public function testReplaceEntities(): void
    {
        $dateTime1 = $this->createMock(DateTime::class);
        $dateTime2 = $this->createMock(DateTime::class);
        $dateTime3 = $this->createMock(DateTime::class);
        $dateTime4 = $this->createMock(DateTime::class);

        $oldEntity1 = new SidebarEntity();
        $oldEntity1->setType('abc')
                   ->setName('def')
                   ->setLabel('ghi')
                   ->setPinnedPosition(42)
                   ->setLastViewTime($dateTime1);
        $oldEntity2 = new SidebarEntity();
        $oldEntity2->setType('jkl')
                   ->setName('mno')
                   ->setLabel('pqr')
                   ->setPinnedPosition(21)
                   ->setLastViewTime($dateTime2);

        $newEntity1 = new SidebarEntity();
        $newEntity1->setType('abc')
                   ->setName('def')
                   ->setLabel('stu')
                   ->setPinnedPosition(1337)
                   ->setLastViewTime($dateTime3);
        $newEntity2 = new SidebarEntity();
        $newEntity2->setType('vwx')
                   ->setName('yza')
                   ->setLabel('bcd')
                   ->setPinnedPosition(7331)
                   ->setLastViewTime($dateTime4);

        $expectedEntity1 = new SidebarEntity();
        $expectedEntity1->setType('abc')
                        ->setName('def')
                        ->setLabel('stu')
                        ->setPinnedPosition(1337)
                        ->setLastViewTime($dateTime3);

        $setting = new Setting();
        $setting->getSidebarEntities()->add($oldEntity1);
        $setting->getSidebarEntities()->add($oldEntity2);

        $this->entityManager->expects($this->exactly(2))
                            ->method('persist')
                            ->withConsecutive(
                                [$this->logicalAnd($this->identicalTo($oldEntity1), $this->equalTo($expectedEntity1))],
                                [$this->identicalTo($newEntity2)],
                            );
        $this->entityManager->expects($this->once())
                            ->method('remove')
                            ->with($this->identicalTo($oldEntity2));

        $instance = $this->createInstance();
        $instance->replaceEntities($setting, [$newEntity1, $newEntity2]);
    }

    /**
     * @throws PortalApiServerException
     */
    public function testRefreshLabels(): void
    {
        $entity1 = new SidebarEntity();
        $entity1->setType('abc')
                ->setName('def')
                ->setLabel('ghi');
        $entity2 = new SidebarEntity();
        $entity2->setType('jkl')
                ->setName('mno')
                ->setLabel('pqr');
        $entity3 = new SidebarEntity();
        $entity3->setType('stu')
                ->setName('vwx')
                ->setLabel('yza');

        $requestEntity1 = new Entity();
        $requestEntity1->type = 'abc';
        $requestEntity1->name = 'def';
        $requestEntity2 = new Entity();
        $requestEntity2->type = 'jkl';
        $requestEntity2->name = 'mno';
        $requestEntity3 = new Entity();
        $requestEntity3->type = 'stu';
        $requestEntity3->name = 'vwx';

        $responseEntity1 = new GenericEntity();
        $responseEntity1->type = 'abc';
        $responseEntity1->name = 'def';
        $responseEntity1->label = 'ihg';
        $responseEntity2 = new GenericEntity();
        $responseEntity2->type = 'stu';
        $responseEntity2->name = 'vwx';
        $responseEntity2->label = 'azy';

        $combination = new Combination();
        $combination->setId(Uuid::fromString('78de8fa6-424b-479e-99c2-bb719eff1e0d'));

        $setting = new Setting();
        $setting->setCombination($combination)
                ->setLocale('foo');
        $setting->getSidebarEntities()->add($entity1);
        $setting->getSidebarEntities()->add($entity2);
        $setting->getSidebarEntities()->add($entity3);

        $expectedApiRequest = new GenericDetailsRequest();
        $expectedApiRequest->combinationId = '78de8fa6-424b-479e-99c2-bb719eff1e0d';
        $expectedApiRequest->locale = 'foo';
        $expectedApiRequest->entities = [$requestEntity1, $requestEntity2, $requestEntity3];

        $apiResponse = new GenericDetailsResponse();
        $apiResponse->entities = [$responseEntity1, $responseEntity2];

        $this->apiClient->expects($this->once())
                        ->method('sendRequest')
                        ->with($this->equalTo($expectedApiRequest))
                        ->willReturn(new FulfilledPromise($apiResponse));

        $this->entityManager->expects($this->once())
                            ->method('remove')
                            ->with($this->identicalTo($entity2));

        $instance = $this->createInstance();
        $instance->refreshLabels($setting);

        $this->assertSame('ihg', $entity1->getLabel());
        $this->assertSame('azy', $entity3->getLabel());
    }
}
