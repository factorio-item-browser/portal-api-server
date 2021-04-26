<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Repository;

use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\Defaults;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
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
    protected CombinationRepository $combinationRepository;
    protected EntityManagerInterface $entityManager;

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
        $setting->setName(Defaults::SETTING_NAME)
                ->setRecipeMode(Defaults::RECIPE_MODE)
                ->setLocale(Defaults::LOCALE)
                ->setHasData(true);
        return $setting;
    }

    /**
     * Creates a temporary setting for the user.
     * @param User $user
     * @param Combination $combination
     * @return Setting
     * @throws Exception
     */
    public function createTemporarySetting(User $user, Combination $combination): Setting
    {
        $setting = $this->createSetting($user, $combination);
        $setting->setName(Defaults::TEMPORARY_SETTING_NAME)
                ->setIsTemporary(true)
                ->setRecipeMode(Defaults::RECIPE_MODE)
                ->setLocale(Defaults::LOCALE);

        $lastSetting = $user->getLastUsedSetting();
        if ($lastSetting !== null) {
            $setting->setRecipeMode($lastSetting->getRecipeMode())
                    ->setLocale($lastSetting->getLocale());
        }

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
