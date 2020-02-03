<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Repository;

use BluePsyduck\TestHelper\ReflectionTrait;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository;
use FactorioItemBrowser\PortalApi\Server\Repository\UserRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Doctrine\UuidBinaryType;
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
                     ->with($this->identicalTo('u'), $this->identicalTo('s'))
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('from')
                     ->with($this->identicalTo(User::class), $this->identicalTo('u'))
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('leftJoin')
                     ->with($this->identicalTo('u.currentSetting'), $this->identicalTo('s'))
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
     * Tests the createUser method.
     * @covers ::createUser
     */
    public function testCreateUser(): void
    {
        /* @var Setting&MockObject $defaultSetting */
        $defaultSetting = $this->createMock(Setting::class);

        $this->settingRepository->expects($this->once())
                                ->method('createDefaultSetting')
                                ->with($this->equalTo(new User()))
                                ->willReturn($defaultSetting);

        $repository = new UserRepository($this->entityManager, $this->settingRepository);
        $result = $repository->createUser();

        $this->assertSame($defaultSetting, $result->getCurrentSetting());
        $this->assertSame([$defaultSetting], $result->getSettings()->toArray());
    }
}
