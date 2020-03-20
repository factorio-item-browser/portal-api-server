<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
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
        $queryBuilder->select('u', 's', 'c')
                     ->from(User::class, 'u')
                     ->leftJoin('u.currentSetting', 's')
                     ->leftJoin('s.combination', 'c')
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
        $user->setCurrentSetting($defaultSetting);
        return $user;
    }

    /**
     * Persists the user to the database.
     * @param User $user
     */
    public function persist(User $user): void
    {
        $this->entityManager->persist($user);
        if ($user->getCurrentSetting() !== null) {
            $this->entityManager->persist($user->getCurrentSetting());
        }
        $this->entityManager->flush();
    }
}
