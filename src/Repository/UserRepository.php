<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\PortalApi\Server\Constant\RecipeMode;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use Ramsey\Uuid\Doctrine\UuidBinaryType;
use Ramsey\Uuid\Uuid;
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
     * Initializes the repository.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
     */
    public function createUser(): User
    {
        $setting = new Setting();
        $setting->setModNames([Constant::MOD_NAME_BASE])
                ->setRecipeMode(RecipeMode::HYBRID)
                ->setCombinationId(Uuid::fromString('2F4A45FAA509A9D1AAE6FFCF984A7A76'));

        $user = new User();
        $user->setCurrentSetting($setting);
        $user->getSettings()->add($setting);

        $setting->setUser($user);

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
