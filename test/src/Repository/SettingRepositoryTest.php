<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Repository;

use BluePsyduck\TestHelper\ReflectionTrait;
use DateTime;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\RecipeMode;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Repository\CombinationRepository;
use FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ReflectionException;

/**
 * The PHPUnit test of the SettingRepository class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository
 */
class SettingRepositoryTest extends TestCase
{
    use ReflectionTrait;

    /** @var CombinationRepository&MockObject */
    private CombinationRepository $combinationRepository;
    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->combinationRepository = $this->createMock(CombinationRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return SettingRepository&MockObject
     */
    private function createInstance(array $mockedMethods = []): SettingRepository
    {
        return $this->getMockBuilder(SettingRepository::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->combinationRepository,
                        $this->entityManager,
                    ])
                    ->getMock();
    }

    /**
     * @throws Exception
     */
    public function testCreateSetting(): void
    {
        /* @var User&MockObject $user */
        $user = $this->createMock(User::class);
        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);

        $instance = $this->createInstance();
        $result = $instance->createSetting($user, $combination);

        $this->assertNotSame('', $result->getId()->toString());
        $this->assertSame($user, $result->getUser());
        $this->assertSame($combination, $result->getCombination());
    }

    /**
     * @throws Exception
     */
    public function testCreateDefaultSetting(): void
    {
        /* @var User&MockObject $user */
        $user = $this->createMock(User::class);
        /* @var Combination&MockObject $$defaultCombination */
        $defaultCombination = $this->createMock(Combination::class);

        $this->combinationRepository->expects($this->once())
                                    ->method('getDefaultCombination')
                                    ->willReturn($defaultCombination);

        $instance = $this->createInstance();
        $result = $instance->createDefaultSetting($user);

        $this->assertNotSame('', $result->getId()->toString());
        $this->assertSame($user, $result->getUser());
        $this->assertSame($defaultCombination, $result->getCombination());
        $this->assertSame('Vanilla', $result->getName());
        $this->assertSame(RecipeMode::HYBRID, $result->getRecipeMode());
        $this->assertSame('en', $result->getLocale());
        $this->assertTrue($result->getHasData());
    }

    /**
     * @throws Exception
     */
    public function testCreateTemporarySetting(): void
    {
        $combination = $this->createMock(Combination::class);

        $lastSetting = new Setting();
        $lastSetting->setLocale('abc')
                    ->setRecipeMode('def');

        $user = $this->createMock(User::class);
        $user->expects($this->once())
             ->method('getLastUsedSetting')
             ->willReturn($lastSetting);

        $instance = $this->createInstance();
        $result = $instance->createTemporarySetting($user, $combination);

        $this->assertNotSame('', $result->getId()->toString());
        $this->assertSame($user, $result->getUser());
        $this->assertSame($combination, $result->getCombination());
        $this->assertSame('Temporary', $result->getName());
        $this->assertSame('def', $result->getRecipeMode());
        $this->assertSame('abc', $result->getLocale());
        $this->assertTrue($result->getIsTemporary());
    }

    /**
     * @throws Exception
     */
    public function testCreateTemporarySettingWithoutLastSetting(): void
    {
        $combination = $this->createMock(Combination::class);

        $user = $this->createMock(User::class);
        $user->expects($this->once())
             ->method('getLastUsedSetting')
             ->willReturn(null);

        $instance = $this->createInstance();
        $result = $instance->createTemporarySetting($user, $combination);

        $this->assertNotSame('', $result->getId()->toString());
        $this->assertSame($user, $result->getUser());
        $this->assertSame($combination, $result->getCombination());
        $this->assertSame('Temporary', $result->getName());
        $this->assertSame(RecipeMode::HYBRID, $result->getRecipeMode());
        $this->assertSame('en', $result->getLocale());
        $this->assertTrue($result->getIsTemporary());
    }

    public function testDeleteSetting(): void
    {
        $settingId = $this->createMock(UuidInterface::class);

        $setting = new Setting();
        $setting->setId($settingId);

        $instance = $this->createInstance(['removeSettings']);
        $instance->expects($this->once())
                 ->method('removeSettings')
                 ->with($this->equalTo([$settingId]));

        $instance->deleteSetting($setting);
    }

    public function testCleanupTemporarySettings(): void
    {
        $timeCut = $this->createMock(DateTime::class);
        $settingIds = [
            $this->createMock(UuidInterface::class),
            $this->createMock(UuidInterface::class),
        ];

        $instance = $this->createInstance(['findOldTemporarySettings', 'removeSettings']);
        $instance->expects($this->once())
                 ->method('findOldTemporarySettings')
                 ->with($this->identicalTo($timeCut))
                 ->willReturn($settingIds);
        $instance->expects($this->once())
                 ->method('removeSettings')
                 ->with($this->identicalTo($settingIds));

        $instance->cleanupTemporarySettings($timeCut);
    }

    public function testCleanupTemporarySettingsWithoutSettingIds(): void
    {
        $timeCut = $this->createMock(DateTime::class);

        $instance = $this->createInstance(['findOldTemporarySettings', 'removeSettings']);
        $instance->expects($this->once())
                 ->method('findOldTemporarySettings')
                 ->with($this->identicalTo($timeCut))
                 ->willReturn([]);
        $instance->expects($this->never())
                 ->method('removeSettings');

        $instance->cleanupTemporarySettings($timeCut);
    }

    /**
     * @throws ReflectionException
     */
    public function testFindOldTemporarySettings(): void
    {
        $timeCut = $this->createMock(DateTime::class);
        $settingId1 = $this->createMock(UuidInterface::class);
        $settingId2 = $this->createMock(UuidInterface::class);

        $queryResult = [
            ['id' => $settingId1],
            ['id' => $settingId2],
        ];
        $expectedResult = [$settingId1, $settingId2];

        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
              ->method('getResult')
              ->willReturn($queryResult);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())
                     ->method('select')
                     ->with($this->identicalTo('s.id'))
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('from')
                     ->with($this->identicalTo(Setting::class), $this->identicalTo('s'))
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly(2))
                     ->method('andWhere')
                     ->withConsecutive(
                         [$this->identicalTo('s.isTemporary = :temporary')],
                         [$this->identicalTo('s.lastUsageTime < :timeCut')],
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly(2))
                     ->method('setParameter')
                     ->withConsecutive(
                         [$this->identicalTo('temporary'), $this->isTrue()],
                         [$this->identicalTo('timeCut'), $this->identicalTo($timeCut)]
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        $this->entityManager->expects($this->once())
                            ->method('createQueryBuilder')
                            ->willReturn($queryBuilder);

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'findOldTemporarySettings', $timeCut);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testRemoveSettings(): void
    {
        $settingIds = [
            Uuid::fromString('693d481c-9b6e-4118-9dc0-7e5d6cd29776'),
            Uuid::fromString('f4639458-0490-4ce4-92ee-71197aae0551'),
        ];
        $expectedMappedSettingIds = [
            hex2bin('693d481c9b6e41189dc07e5d6cd29776'),
            hex2bin('f463945804904ce492ee71197aae0551'),
        ];

        $query1 = $this->createMock(AbstractQuery::class);
        $query1->expects($this->once())
               ->method('execute');

        $queryBuilder1 = $this->createMock(QueryBuilder::class);
        $queryBuilder1->expects($this->once())
                      ->method('delete')
                      ->with($this->identicalTo(SidebarEntity::class), $this->identicalTo('se'))
                      ->willReturnSelf();
        $queryBuilder1->expects($this->once())
                      ->method('where')
                      ->with($this->identicalTo('se.setting IN (:settingIds)'))
                      ->willReturnSelf();
        $queryBuilder1->expects($this->once())
                      ->method('setParameter')
                      ->with($this->identicalTo('settingIds'), $this->identicalTo($expectedMappedSettingIds))
                      ->willReturnSelf();
        $queryBuilder1->expects($this->once())
                      ->method('getQuery')
                      ->willReturn($query1);

        $query2 = $this->createMock(AbstractQuery::class);
        $query2->expects($this->once())
               ->method('execute');

        $queryBuilder2 = $this->createMock(QueryBuilder::class);
        $queryBuilder2->expects($this->once())
                      ->method('delete')
                      ->with($this->identicalTo(Setting::class), $this->identicalTo('s'))
                      ->willReturnSelf();
        $queryBuilder2->expects($this->once())
                      ->method('where')
                      ->with($this->identicalTo('s.id IN (:settingIds)'))
                      ->willReturnSelf();
        $queryBuilder2->expects($this->once())
                      ->method('setParameter')
                      ->with($this->identicalTo('settingIds'), $this->identicalTo($expectedMappedSettingIds))
                      ->willReturnSelf();
        $queryBuilder2->expects($this->once())
                      ->method('getQuery')
                      ->willReturn($query2);

        $this->entityManager->expects($this->exactly(2))
                            ->method('createQueryBuilder')
                            ->willReturnOnConsecutiveCalls(
                                $queryBuilder1,
                                $queryBuilder2
                            );

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'removeSettings', $settingIds);
    }
}
