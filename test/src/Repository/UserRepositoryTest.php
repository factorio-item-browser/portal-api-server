<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Repository;

use BluePsyduck\TestHelper\ReflectionTrait;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository;
use FactorioItemBrowser\PortalApi\Server\Repository\UserRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Doctrine\UuidBinaryType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ReflectionException;

/**
 * The PHPUnit test of the UserRepository class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Repository\UserRepository
 */
class UserRepositoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked entity manager.
     * @var EntityManagerInterface&MockObject
     */
    protected $entityManager;

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

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->settingRepository = $this->createMock(SettingRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $repository = new UserRepository($this->entityManager, $this->settingRepository);

        $this->assertSame($this->entityManager, $this->extractProperty($repository, 'entityManager'));
        $this->assertSame($this->settingRepository, $this->extractProperty($repository, 'settingRepository'));
    }

    /**
     * Tests the getUser method.
     * @covers ::getUser
     */
    public function testGetUser(): void
    {
        /* @var UuidInterface&MockObject $userId */
        $userId = $this->createMock(UuidInterface::class);
        /* @var User&MockObject $user */
        $user = $this->createMock(User::class);

        /* @var AbstractQuery&MockObject $query */
        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
              ->method('getOneOrNullResult')
              ->willReturn($user);

        /* @var QueryBuilder&MockObject $queryBuilder */
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())
                     ->method('select')
                     ->with($this->identicalTo('u'), $this->identicalTo('s'), $this->identicalTo('c'))
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('from')
                     ->with($this->identicalTo(User::class), $this->identicalTo('u'))
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly(2))
                     ->method('leftJoin')
                     ->withConsecutive(
                         [$this->identicalTo('u.currentSetting'), $this->identicalTo('s')],
                         [$this->identicalTo('s.combination'), $this->identicalTo('c')]
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('where')
                     ->with($this->identicalTo('u.id = :userId'))
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('setParameter')
                     ->with(
                         $this->identicalTo('userId'),
                         $this->identicalTo($userId),
                         $this->identicalTo(UuidBinaryType::NAME)
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        $this->entityManager->expects($this->once())
                            ->method('createQueryBuilder')
                            ->willReturn($queryBuilder);

        $repository = new UserRepository($this->entityManager, $this->settingRepository);
        $result = $repository->getUser($userId);

        $this->assertSame($user, $result);
    }

    /**
     * Tests the getUser method.
     * @covers ::getUser
     */
    public function testGetUserWithException(): void
    {
        /* @var UuidInterface&MockObject $userId */
        $userId = $this->createMock(UuidInterface::class);

        /* @var AbstractQuery&MockObject $query */
        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
              ->method('getOneOrNullResult')
              ->willThrowException($this->createMock(NonUniqueResultException::class));

        /* @var QueryBuilder&MockObject $queryBuilder */
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())
                     ->method('select')
                     ->with($this->identicalTo('u'), $this->identicalTo('s'), $this->identicalTo('c'))
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('from')
                     ->with($this->identicalTo(User::class), $this->identicalTo('u'))
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly(2))
                     ->method('leftJoin')
                     ->withConsecutive(
                         [$this->identicalTo('u.currentSetting'), $this->identicalTo('s')],
                         [$this->identicalTo('s.combination'), $this->identicalTo('c')]
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('where')
                     ->with($this->identicalTo('u.id = :userId'))
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('setParameter')
                     ->with(
                         $this->identicalTo('userId'),
                         $this->identicalTo($userId),
                         $this->identicalTo(UuidBinaryType::NAME)
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);


        $this->entityManager->expects($this->once())
                            ->method('createQueryBuilder')
                            ->willReturn($queryBuilder);

        $repository = new UserRepository($this->entityManager, $this->settingRepository);
        $result = $repository->getUser($userId);

        $this->assertNull($result);
    }

    /**
     * Tests the createUser method.
     * @covers ::createUser
     * @throws Exception
     */
    public function testCreateUser(): void
    {
        /* @var Setting&MockObject $defaultSetting */
        $defaultSetting = $this->createMock(Setting::class);

        $this->entityManager->expects($this->once())
                            ->method('persist')
                            ->with($this->equalTo(new User()));
        $this->entityManager->expects($this->once())
                            ->method('flush');

        $this->settingRepository->expects($this->once())
                                ->method('createDefaultSetting')
                                ->with($this->equalTo(new User()))
                                ->willReturn($defaultSetting);

        $repository = new UserRepository($this->entityManager, $this->settingRepository);
        $result = $repository->createUser();

        $this->assertSame($defaultSetting, $result->getCurrentSetting());
        $this->assertSame([$defaultSetting], $result->getSettings()->toArray());
    }

    /**
     * Tests the persist method.
     * @throws Exception
     * @covers ::persist
     */
    public function testPersist(): void
    {
        /* @var Setting&MockObject $currentSetting */
        $currentSetting = $this->createMock(Setting::class);

        /* @var User&MockObject $user */
        $user = $this->createMock(User::class);
        $user->expects($this->once())
             ->method('setLastVisitTime')
             ->with($this->isInstanceOf(DateTime::class));
        $user->expects($this->any())
             ->method('getCurrentSetting')
             ->willReturn($currentSetting);

        $this->entityManager->expects($this->exactly(2))
                            ->method('persist')
                            ->withConsecutive(
                                [$this->identicalTo($user)],
                                [$this->identicalTo($currentSetting)]
                            );
        $this->entityManager->expects($this->once())
                            ->method('flush');

        $repository = new UserRepository($this->entityManager, $this->settingRepository);
        $repository->persist($user);
    }

    /**
     * Tests the cleanupOldSessions method.
     * @throws Exception
     * @covers ::cleanupOldSessions
     */
    public function testCleanupOldSessions(): void
    {
        /* @var DateTime&MockObject $timeCut */
        $timeCut = $this->createMock(DateTime::class);
        $userIds = [
            $this->createMock(UuidInterface::class),
            $this->createMock(UuidInterface::class),
        ];

        /* @var UserRepository&MockObject $repository */
        $repository = $this->getMockBuilder(UserRepository::class)
                           ->onlyMethods(['findUserIdsWithOldSession', 'removeUsers'])
                           ->setConstructorArgs([$this->entityManager, $this->settingRepository])
                           ->getMock();
        $repository->expects($this->once())
                   ->method('findUserIdsWithOldSession')
                   ->with($this->identicalTo($timeCut))
                   ->willReturn($userIds);
        $repository->expects($this->once())
                   ->method('removeUsers')
                   ->with($this->identicalTo($userIds));

        $repository->cleanupOldSessions($timeCut);
    }

    /**
     * Tests the cleanupOldSessions method.
     * @throws Exception
     * @covers ::cleanupOldSessions
     */
    public function testCleanupOldSessionsWithoutUserIds(): void
    {
        /* @var DateTime&MockObject $timeCut */
        $timeCut = $this->createMock(DateTime::class);

        /* @var UserRepository&MockObject $repository */
        $repository = $this->getMockBuilder(UserRepository::class)
                           ->onlyMethods(['findUserIdsWithOldSession', 'removeUsers'])
                           ->setConstructorArgs([$this->entityManager, $this->settingRepository])
                           ->getMock();
        $repository->expects($this->once())
                   ->method('findUserIdsWithOldSession')
                   ->with($this->identicalTo($timeCut))
                   ->willReturn([]);
        $repository->expects($this->never())
                   ->method('removeUsers');

        $repository->cleanupOldSessions($timeCut);
    }

    /**
     * Tests the findUserIdsWithOldSession method.
     * @throws ReflectionException
     * @covers ::findUserIdsWithOldSession
     */
    public function testFindUserIdsWithOldSession(): void
    {
        /* @var DateTime&MockObject $timeCut */
        $timeCut = $this->createMock(DateTime::class);
        /* @var UuidInterface&MockObject $userId1 */
        $userId1 = $this->createMock(UuidInterface::class);
        /* @var UuidInterface&MockObject $userId2 */
        $userId2 = $this->createMock(UuidInterface::class);

        $queryResult = [
            ['id' => $userId1],
            ['id' => $userId2],
        ];
        $expectedResult = [$userId1, $userId2];

        /* @var AbstractQuery&MockObject $query */
        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
              ->method('getResult')
              ->willReturn($queryResult);

        /* @var QueryBuilder&MockObject $queryBuilder */
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())
                     ->method('select')
                     ->with($this->identicalTo('u.id'))
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('from')
                     ->with($this->identicalTo(User::class), $this->identicalTo('u'))
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('where')
                     ->with($this->identicalTo('u.lastVisitTime < :timeCut'))
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('setParameter')
                     ->with($this->identicalTo('timeCut'), $this->identicalTo($timeCut))
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        $this->entityManager->expects($this->once())
                            ->method('createQueryBuilder')
                            ->willReturn($queryBuilder);

        $repository = new UserRepository($this->entityManager, $this->settingRepository);
        $result = $this->invokeMethod($repository, 'findUserIdsWithOldSession', $timeCut);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the removeUsers method.
     * @throws ReflectionException
     * @covers ::removeUsers
     */
    public function testRemoveUsers(): void
    {
        $userIds = [
            Uuid::fromString('693d481c-9b6e-4118-9dc0-7e5d6cd29776'),
            Uuid::fromString('f4639458-0490-4ce4-92ee-71197aae0551'),
        ];
        $expectedMappedUserIds = [
            hex2bin('693d481c9b6e41189dc07e5d6cd29776'),
            hex2bin('f463945804904ce492ee71197aae0551'),
        ];
        
        /* @var AbstractQuery&MockObject $query1 */
        $query1 = $this->createMock(AbstractQuery::class);
        $query1->expects($this->once())
               ->method('execute');
        
        /* @var QueryBuilder&MockObject $queryBuilder1 */
        $queryBuilder1 = $this->createMock(QueryBuilder::class);
        $queryBuilder1->expects($this->once())
                      ->method('update')
                      ->with($this->identicalTo(User::class), $this->identicalTo('u'))
                      ->willReturnSelf();
        $queryBuilder1->expects($this->once())
                      ->method('set')
                      ->with($this->identicalTo('u.currentSetting'), $this->identicalTo('NULL'))
                      ->willReturnSelf();
        $queryBuilder1->expects($this->once())
                      ->method('where')
                      ->with($this->identicalTo('u.id IN (:userIds)'))
                      ->willReturnSelf();
        $queryBuilder1->expects($this->once())
                      ->method('setParameter')
                      ->with($this->identicalTo('userIds'), $this->identicalTo($expectedMappedUserIds))
                      ->willReturnSelf();
        $queryBuilder1->expects($this->once())
                      ->method('getQuery')
                      ->willReturn($query1);
        
        /* @var AbstractQuery&MockObject $query3 */
        $query3 = $this->createMock(AbstractQuery::class);
        $query3->expects($this->once())
               ->method('execute');
        
        /* @var QueryBuilder&MockObject $queryBuilder3 */
        $queryBuilder3 = $this->createMock(QueryBuilder::class);
        $queryBuilder3->expects($this->once())
                      ->method('delete')
                      ->with($this->identicalTo(Setting::class), $this->identicalTo('s'))
                      ->willReturnSelf();
        $queryBuilder3->expects($this->once())
                      ->method('where')
                      ->with($this->identicalTo('s.user IN (:userIds)'))
                      ->willReturnSelf();
        $queryBuilder3->expects($this->once())
                      ->method('setParameter')
                      ->with($this->identicalTo('userIds'), $this->identicalTo($expectedMappedUserIds))
                      ->willReturnSelf();
        $queryBuilder3->expects($this->once())
                      ->method('getQuery')
                      ->willReturn($query3);
        
        /* @var AbstractQuery&MockObject $query4 */
        $query4 = $this->createMock(AbstractQuery::class);
        $query4->expects($this->once())
               ->method('execute');
        
        /* @var QueryBuilder&MockObject $queryBuilder4 */
        $queryBuilder4 = $this->createMock(QueryBuilder::class);
        $queryBuilder4->expects($this->once())
                      ->method('delete')
                      ->with($this->identicalTo(User::class), $this->identicalTo('u'))
                      ->willReturnSelf();
        $queryBuilder4->expects($this->once())
                      ->method('where')
                      ->with($this->identicalTo('u.id IN (:userIds)'))
                      ->willReturnSelf();
        $queryBuilder4->expects($this->once())
                      ->method('setParameter')
                      ->with($this->identicalTo('userIds'), $this->identicalTo($expectedMappedUserIds))
                      ->willReturnSelf();
        $queryBuilder4->expects($this->once())
                      ->method('getQuery')
                      ->willReturn($query4);

        $this->entityManager->expects($this->exactly(3))
                            ->method('createQueryBuilder')
                            ->willReturnOnConsecutiveCalls(
                                $queryBuilder1,
                                $queryBuilder3,
                                $queryBuilder4
                            );

        /* @var UserRepository&MockObject $repository */
        $repository = $this->getMockBuilder(UserRepository::class)
                           ->onlyMethods(['removeSidebarEntities'])
                           ->setConstructorArgs([$this->entityManager, $this->settingRepository])
                           ->getMock();
        $repository->expects($this->once())
                   ->method('removeSidebarEntities')
                   ->with($this->identicalTo($userIds));

        $this->invokeMethod($repository, 'removeUsers', $userIds);
    }

    /**
     * Tests the removeSidebarEntities method.
     * @throws ReflectionException
     * @covers ::removeSidebarEntities
     */
    public function testRemoveSidebarEntities(): void
    {
        $userIds = [
            Uuid::fromString('693d481c-9b6e-4118-9dc0-7e5d6cd29776'),
            Uuid::fromString('f4639458-0490-4ce4-92ee-71197aae0551'),
        ];
        $expectedMappedUserIds = [
            '693d481c9b6e41189dc07e5d6cd29776',
            'f463945804904ce492ee71197aae0551',
        ];

        $expectedQuery = 'DELETE se FROM `SidebarEntity` se INNER JOIN `Setting` s ON s.id = se.settingId '
            . 'WHERE s.userId IN (UNHEX(?),UNHEX(?))';

        /* @var Statement&MockObject $statement */
        $statement = $this->createMock(Statement::class);
        $statement->expects($this->once())
                  ->method('execute')
                  ->with($this->identicalTo($expectedMappedUserIds));

        /* @var Connection&MockObject $connection */
        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
                   ->method('prepare')
                   ->with($this->identicalTo($expectedQuery))
                   ->willReturn($statement);

        $this->entityManager->expects($this->once())
                            ->method('getConnection')
                            ->willReturn($connection);

        $repository = new UserRepository($this->entityManager, $this->settingRepository);
        $this->invokeMethod($repository, 'removeSidebarEntities', $userIds);
    }
}
