<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Repository;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use Ramsey\Uuid\Doctrine\UuidBinaryType;
use Ramsey\Uuid\UuidInterface;

/**
 * The repository of the users.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UserRepository
{
    /**
     * The entity manager.
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * The setting repository.
     * @var SettingRepository
     */
    protected $settingRepository;

    /**
     * Initializes the repository.
     * @param EntityManagerInterface $entityManager
     * @param SettingRepository $settingRepository
     */
    public function __construct(EntityManagerInterface $entityManager, SettingRepository $settingRepository)
    {
        $this->entityManager = $entityManager;
        $this->settingRepository = $settingRepository;
    }

    /**
     * Returns the user with the specified user id, if available.
     * @param UuidInterface $userId
     * @return User|null
     */
    public function getUser(UuidInterface $userId): ?User
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('u')
                     ->from(User::class, 'u')
                     ->where('u.id = :userId')
                     ->setParameter('userId', $userId, UuidBinaryType::NAME);

        try {
            return $queryBuilder->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            // Can never happen, we are searching for the primary key.
            return null;
        }
    }

    /**
     * Creates a new user with default settings with the specified user id.
     * @return User
     * @throws Exception
     */
    public function createUser(): User
    {
        $user = new User();
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $defaultSetting = $this->settingRepository->createDefaultSetting($user);

        $user->getSettings()->add($defaultSetting);
        return $user;
    }

    /**
     * Persists the user to the database.
     * @param User $user
     * @throws Exception
     */
    public function persist(User $user): void
    {
        $user->setLastVisitTime(new DateTime());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Cleans up old sessions.
     * @param DateTimeInterface $timeCut
     * @throws DriverException
     * @throws Exception
     */
    public function cleanupOldSessions(DateTimeInterface $timeCut): void
    {
        $userIds = $this->findUserIdsWithOldSession($timeCut);
        if (count($userIds) > 0) {
            $this->removeUsers($userIds);
        }
    }

    /**
     * Searches for users with old sessions and returns their ids.
     * @param DateTimeInterface $timeCut
     * @return array<UuidInterface>|UuidInterface[]
     */
    protected function findUserIdsWithOldSession(DateTimeInterface $timeCut): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('u.id')
                     ->from(User::class, 'u')
                     ->where('u.lastVisitTime < :timeCut')
                     ->setParameter('timeCut', $timeCut);

        $result = [];
        foreach ($queryBuilder->getQuery()->getResult() as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    /**
     * Removes all users with the specified ids from the database.
     * @param array<UuidInterface>|UuidInterface[] $userIds
     * @throws DriverException
     */
    public function removeUsers(array $userIds): void
    {
        $mappedUserIds = array_values(array_map(function (UuidInterface $userId): string {
            return $userId->getBytes();
        }, $userIds));

        // 1. Remove all sidebar entities of the users.
        $this->removeSidebarEntities($userIds);

        // 2. Remove all settings of the users.
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->delete(Setting::class, 's')
                     ->where('s.user IN (:userIds)')
                     ->setParameter('userIds', $mappedUserIds);
        $queryBuilder->getQuery()->execute();

        // 3. Remove the actual users.
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->delete(User::class, 'u')
                     ->where('u.id IN (:userIds)')
                     ->setParameter('userIds', $mappedUserIds);
        $queryBuilder->getQuery()->execute();
    }

    /**
     * Removes the sidebar entities of the specified user ids.
     * Note: DQL does not support JOINs in DELETE statements, so we have to use a native query instead.
     * @param array<UuidInterface>|UuidInterface[] $userIds
     * @throws DriverException
     * @throws Exception
     */
    protected function removeSidebarEntities(array $userIds): void
    {
        $mappedUserIds = array_values(array_map(function (UuidInterface $userId): string {
            return $userId->getHex()->toString();
        }, $userIds));
        $placeholders = implode(',', array_fill(0, count($userIds), 'UNHEX(?)'));

        $query = "DELETE se FROM `SidebarEntity` se INNER JOIN `Setting` s ON s.id = se.settingId "
            . "WHERE s.userId IN ({$placeholders})";

        $statement = $this->entityManager->getConnection()->prepare($query);
        $statement->execute($mappedUserIds);
    }
}
