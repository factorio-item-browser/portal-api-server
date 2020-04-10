<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\RecipeMode;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use Ramsey\Uuid\Uuid;

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
                ->setLocale(self::DEFAULT_LOCALE);
        return $setting;
    }

    /**
     * Deletes the specified setting from the database.
     * @param Setting $setting
     */
    public function deleteSetting(Setting $setting): void
    {
        foreach ($setting->getSidebarEntities() as $sidebarEntity) {
            $this->entityManager->remove($sidebarEntity);
        }
        $this->entityManager->remove($setting);
    }
}
