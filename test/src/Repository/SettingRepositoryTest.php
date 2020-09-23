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
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
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
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository
 */
class SettingRepositoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked combination repository.
     * @var CombinationRepository&MockObject
     */
    protected $combinationRepository;

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

        $this->combinationRepository = $this->createMock(CombinationRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $repository = new SettingRepository($this->combinationRepository, $this->entityManager);

        $this->assertSame($this->combinationRepository, $this->extractProperty($repository, 'combinationRepository'));
        $this->assertSame($this->entityManager, $this->extractProperty($repository, 'entityManager'));
    }

    /**
     * Tests the createSetting method.
     * @covers ::createSetting
     * @throws Exception
     */
    public function testCreateSetting(): void
    {
        /* @var User&MockObject $user */
        $user = $this->createMock(User::class);
        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);

        $repository = new SettingRepository($this->combinationRepository, $this->entityManager);
        $result = $repository->createSetting($user, $combination);

        $result->getId(); // Asserted by type-hint.
        $this->assertSame($user, $result->getUser());
        $this->assertSame($combination, $result->getCombination());
    }

    /**
     * Tests the createDefaultSetting method.
     * @covers ::createDefaultSetting
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

        $repository = new SettingRepository($this->combinationRepository, $this->entityManager);
        $result = $repository->createDefaultSetting($user);

        $result->getId(); // Asserted by type-hint.
        $this->assertSame($user, $result->getUser());
        $this->assertSame($defaultCombination, $result->getCombination());
        $this->assertSame('Vanilla', $result->getName());
        $this->assertSame(RecipeMode::HYBRID, $result->getRecipeMode());
        $this->assertSame('en', $result->getLocale());
        $this->assertTrue($result->getHasData());
    }

    /**
     * Tests the createTemporarySetting method.
     * @throws Exception
     * @covers ::createTemporarySetting
     */
    public function testCreateTemporarySetting(): void
    {
        $combinationId = $this->createMock(UuidInterface::class);
        $combination = $this->createMock(Combination::class);

        $lastSetting = new Setting();
        $lastSetting->setLocale('abc')
                    ->setRecipeMode('def');

        $user = $this->createMock(User::class);
        $user->expects($this->once())
             ->method('getLastUsedSetting')
             ->willReturn($lastSetting);

        $this->combinationRepository->expects($this->once())
                                    ->method('getCombination')
                                    ->with($this->identicalTo($combinationId))
                                    ->willReturn($combination);

        $repository = new SettingRepository($this->combinationRepository, $this->entityManager);
        $result = $repository->createTemporarySetting($user, $combinationId);

        $result->getId(); // Asserted by type-hint.
        $this->assertSame($user, $result->getUser());
        $this->assertSame($combination, $result->getCombination());
        $this->assertSame('Temporary', $result->getName());
        $this->assertSame('def', $result->getRecipeMode());
        $this->assertSame('abc', $result->getLocale());
        $this->assertTrue($result->getIsTemporary());
    }

    /**
     * Tests the createTemporarySetting method.
     * @throws Exception
     * @covers ::createTemporarySetting
     */
    public function testCreateTemporarySettingWithoutLastSetting(): void
    {
        $combinationId = $this->createMock(UuidInterface::class);
        $combination = $this->createMock(Combination::class);

        $user = $this->createMock(User::class);
        $user->expects($this->once())
             ->method('getLastUsedSetting')
             ->willReturn(null);

        $this->combinationRepository->expects($this->once())
                                    ->method('getCombination')
                                    ->with($this->identicalTo($combinationId))
                                    ->willReturn($combination);
        
        $repository = new SettingRepository($this->combinationRepository, $this->entityManager);
        $result = $repository->createTemporarySetting($user, $combinationId);

        $result->getId(); // Asserted by type-hint.
        $this->assertSame($user, $result->getUser());
        $this->assertSame($combination, $result->getCombination());
        $this->assertSame('Temporary', $result->getName());
        $this->assertSame(RecipeMode::HYBRID, $result->getRecipeMode());
        $this->assertSame('en', $result->getLocale());
        $this->assertTrue($result->getIsTemporary());
    }

    /**
     * Tests the createTemporarySetting method.
     * @throws Exception
     * @covers ::createTemporarySetting
     */
    public function testCreateTemporarySettingWithoutCombination(): void
    {
        $user = $this->createMock(User::class);
        $combinationId = Uuid::fromString('800a2e58-034d-414e-8bb0-056bb9d5b4b0');

        $this->combinationRepository->expects($this->once())
                                    ->method('getCombination')
                                    ->with($this->identicalTo($combinationId))
                                    ->willReturn(null);

        $this->expectException(UnknownEntityException::class);

        $repository = new SettingRepository($this->combinationRepository, $this->entityManager);
        $repository->createTemporarySetting($user, $combinationId);
    }

    /**
     * Tests the deleteSetting method.
     * @covers ::deleteSetting
     */
    public function testDeleteSetting(): void
    {
        $settingId = $this->createMock(UuidInterface::class);

        $setting = new Setting();
        $setting->setId($settingId);

        $repository = $this->getMockBuilder(SettingRepository::class)
                           ->onlyMethods(['removeSettings'])
                           ->setConstructorArgs([$this->combinationRepository, $this->entityManager])
                           ->getMock();
        $repository->expects($this->once())
                   ->method('removeSettings')
                   ->with($this->equalTo([$settingId]));

        $repository->deleteSetting($setting);
    }

    /**
     * Tests the cleanupTemporarySettings method.
     * @covers ::cleanupTemporarySettings
     */
    public function testCleanupTemporarySettings(): void
    {
        $timeCut = $this->createMock(DateTime::class);
        $settingIds = [
            $this->createMock(UuidInterface::class),
            $this->createMock(UuidInterface::class),
        ];

        $repository = $this->getMockBuilder(SettingRepository::class)
                           ->onlyMethods(['findOldTemporarySettings', 'removeSettings'])
                           ->setConstructorArgs([$this->combinationRepository, $this->entityManager])
                           ->getMock();
        $repository->expects($this->once())
                   ->method('findOldTemporarySettings')
                   ->with($this->identicalTo($timeCut))
                   ->willReturn($settingIds);
        $repository->expects($this->once())
                   ->method('removeSettings')
                   ->with($this->identicalTo($settingIds));

        $repository->cleanupTemporarySettings($timeCut);
    }

    /**
     * Tests the cleanupTemporarySettings method.
     * @covers ::cleanupTemporarySettings
     */
    public function testCleanupTemporarySettingsWithoutSettingIds(): void
    {
        $timeCut = $this->createMock(DateTime::class);

        $repository = $this->getMockBuilder(SettingRepository::class)
                           ->onlyMethods(['findOldTemporarySettings', 'removeSettings'])
                           ->setConstructorArgs([$this->combinationRepository, $this->entityManager])
                           ->getMock();
        $repository->expects($this->once())
                   ->method('findOldTemporarySettings')
                   ->with($this->identicalTo($timeCut))
                   ->willReturn([]);
        $repository->expects($this->never())
                   ->method('removeSettings');

        $repository->cleanupTemporarySettings($timeCut);
    }

    /**
     * Tests the findOldTemporarySettings method.
     * @throws ReflectionException
     * @covers ::findOldTemporarySettings
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

        $repository = new SettingRepository($this->combinationRepository, $this->entityManager);
        $result = $this->invokeMethod($repository, 'findOldTemporarySettings', $timeCut);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the removeSettings method.
     * @throws ReflectionException
     * @covers ::removeSettings
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

        $repository = new SettingRepository($this->combinationRepository, $this->entityManager);
        $this->invokeMethod($repository, 'removeSettings', $settingIds);
    }
}
