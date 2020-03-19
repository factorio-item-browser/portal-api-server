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
     * @param string $name
     * @return Setting
     * @throws Exception
     */
    public function createSetting(User $user, Combination $combination, string $name): Setting
    {
        $setting = new Setting();
        $setting->setId(Uuid::uuid4())
                ->setUser($user)
                ->setCombination($combination)
                ->setName($name);

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
        $setting = $this->createSetting($user, $defaultCombination, self::DEFAULT_NAME);
        $setting->setRecipeMode(self::DEFAULT_RECIPE_MODE)
                ->setLocale(self::DEFAULT_LOCALE);
        return $setting;
    }
}
