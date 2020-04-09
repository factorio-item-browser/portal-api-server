<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use Ramsey\Uuid\Doctrine\UuidBinaryType;
use Ramsey\Uuid\UuidInterface;

/**
 * The repository of the combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationRepository
{
    /**
     * The combination id of the default setting.
     */
    protected const DEFAULT_COMBINATION_ID = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';

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
     * Returns the default combination from the database.
     * @return Combination
     * @throws PortalApiServerException
     */
    public function getDefaultCombination(): Combination
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('c')
                     ->from(Combination::class, 'c')
                     ->where('c.id = :combinationId')
                     ->setParameter('combinationId', self::DEFAULT_COMBINATION_ID, UuidBinaryType::NAME);


        try {
            $result = $queryBuilder->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            // Can never happen, we are searching for the primary key.
            $result = null;
        }

        if ($result === null) {
            throw new PortalApiServerException('Missing default combination in database', 500);
        }
        return $result;
    }

    /**
     * Returns the combination with the specified id, if known.
     * @param UuidInterface $combinationId
     * @return Combination|null
     */
    public function getCombination(UuidInterface $combinationId): ?Combination
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('c')
                     ->from(Combination::class, 'c')
                     ->where('c.id = :combinationId')
                     ->setParameter('combinationId', $combinationId, UuidBinaryType::NAME);

        try {
            return $queryBuilder->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            // Can never happen, we are searching for the primary key.
            return null;
        }
    }

    /**
     * Persists the specified combination to the database.
     * @param Combination $combination
     */
    public function persist(Combination $combination): void
    {
        $this->entityManager->persist($combination);
    }
}
