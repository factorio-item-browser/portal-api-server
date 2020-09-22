<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Repository;

use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\RecipeMode;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * The repository of the settings.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingRepository
{
    /**
     * The name of the default setting.
     */
    protected const DEFAULT_NAME = 'Vanilla';

    /**
     * The recipe mode of the default setting.
     */
    protected const DEFAULT_RECIPE_MODE = RecipeMode::HYBRID;

    /**
     * The locale of the default setting.
     */
    protected const DEFAULT_LOCALE = 'en';

    /**
     * The name of a temporary setting.
     */
    protected const TEMPORARY_NAME = 'Temporary';

    /**
     * The combination repository.
     * @var CombinationRepository
     */
    protected $combinationRepository;

    /**
     * The entity manager.
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Initializes the repository.
     * @param CombinationRepository $combinationRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        CombinationRepository $combinationRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->combinationRepository = $combinationRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Creates a new setting for the specified user.
     * @param User $user
     * @param Combination $combination
     * @return Setting
     * @throws Exception
     */
    public function createSetting(User $user, Combination $combination): Setting
    {
        $setting = new Setting();
        $setting->setId(Uuid::uuid4())
                ->setUser($user)
                ->setCombination($combination);

        $this->entityManager->persist($setting);
        return $setting;
    }

    /**
     * Creates a default setting instance for the user.
     * @param User $user
     * @return Setting
     * @throws Exception
     */
    public function createDefaultSetting(User $user): Setting
    {
        $defaultCombination = $this->combinationRepository->getDefaultCombination();
        $setting = $this->createSetting($user, $defaultCombination);
        $setting->setName(self::DEFAULT_NAME)
                ->setRecipeMode(self::DEFAULT_RECIPE_MODE)
                ->setLocale(self::DEFAULT_LOCALE)
                ->setHasData(true);
        return $setting;
    }

    /**
     * Creates a temporary setting for the user.
     * @param User $user
     * @param UuidInterface $combinationId
     * @return Setting
     * @throws Exception
     */
    public function createTemporarySetting(User $user, UuidInterface $combinationId): Setting
    {
        $combination = $this->combinationRepository->getCombination($combinationId);
        if ($combination === null) {
            throw new UnknownEntityException('combination', $combinationId->toString());
        }

        $setting = $this->createSetting($user, $combination);
        $setting->setName(self::TEMPORARY_NAME)
                ->setIsTemporary(true)
                ->setRecipeMode(self::DEFAULT_RECIPE_MODE)
                ->setLocale(self::DEFAULT_LOCALE);
        return $setting;
    }

    /**
     * Deletes the specified setting from the database.
     * @param Setting $setting
     */
    public function deleteSetting(Setting $setting): void
    {
        $this->removeSettings([$setting->getId()]);
    }

    /**
     * Cleans the temporary settings which did not have been used in the specified time.
     * @param DateTimeInterface $timeCut
     */
    public function cleanupTemporarySettings(DateTimeInterface $timeCut): void
    {
        $settingIds = $this->findOldTemporarySettings($timeCut);
        if (count($settingIds) > 0) {
            $this->removeSettings($settingIds);
        }
    }

    /**
     * Searches for old temporary settings which can be removed.
     * @param DateTimeInterface $timeCut
     * @return array<UuidInterface>
     */
    protected function findOldTemporarySettings(DateTimeInterface $timeCut): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('s.id')
                     ->from(Setting::class, 's')
                     ->andWhere('s.isTemporary = :temporary')
                     ->andWhere('s.lastUsageTime < :timeCut')
                     ->setParameter('temporary', true)
                     ->setParameter('timeCut', $timeCut);

        $result = [];
        foreach ($queryBuilder->getQuery()->getResult() as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    /**
     * Removes the settings with the specified ids.
     * @param array<UuidInterface> $settingIds
     */
    protected function removeSettings(array $settingIds): void
    {
        $mappedSettingIds = array_values(array_map(function (UuidInterface $settingId): string {
            return $settingId->getBytes();
        }, $settingIds));

        // 1. Remove all sidebar entities of the settings.
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->delete(SidebarEntity::class, 'se')
                     ->where('se.setting IN (:settingIds)')
                     ->setParameter('settingIds', $mappedSettingIds);
        $queryBuilder->getQuery()->execute();

        // 2. Remove the actual settings.
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->delete(Setting::class, 's')
                     ->where('s.id IN (:settingIds)')
                     ->setParameter('settingIds', $mappedSettingIds);
        $queryBuilder->getQuery()->execute();
    }
}
