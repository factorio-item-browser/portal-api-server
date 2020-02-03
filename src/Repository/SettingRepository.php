<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Repository;

use Doctrine\ORM\EntityManagerInterface;
use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\PortalApi\Server\Constant\RecipeMode;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
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
     * The mod names of the default setting.
     */
    protected const DEFAULT_MOD_NAMES = [Constant::MOD_NAME_BASE];

    /**
     * The combination id of the default setting.
     */
    protected const DEFAULT_COMBINATION_ID = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';

    /**
     * The recipe mode of the default setting.
     */
    protected const DEFAULT_RECIPE_MODE = RecipeMode::HYBRID;

    /**
     * The locale of the default setting.
     */
    protected const DEFAULT_LOCALE = 'en';

    /**
     * The entity manager.
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * The sidebar entity repository.
     * @var SidebarEntityRepository
     */
    protected $sidebarEntityRepository;

    /**
     * Initializes the repository.
     * @param EntityManagerInterface $entityManager
     * @param SidebarEntityRepository $sidebarEntityRepository
     */
    public function __construct(EntityManagerInterface $entityManager, SidebarEntityRepository $sidebarEntityRepository)
    {
        $this->entityManager = $entityManager;
        $this->sidebarEntityRepository = $sidebarEntityRepository;
    }

    /**
     * Creates a new setting for the specified user.
     * @param User $user
     * @param string $name
     * @return Setting
     */
    public function createSetting(User $user, string $name): Setting
    {
        $setting = new Setting();
        $setting->setUser($user)
                /*->setName($name)*/; // @todo Add name to database table.
        return $setting;
    }

    /**
     * Creates a default setting instance for the user.
     * @param User $user
     * @return Setting
     */
    public function createDefaultSetting(User $user): Setting
    {
        $setting = $this->createSetting($user, self::DEFAULT_NAME);
        $setting->setModNames(self::DEFAULT_MOD_NAMES)
                ->setCombinationId(Uuid::fromString(self::DEFAULT_COMBINATION_ID))
                ->setRecipeMode(self::DEFAULT_RECIPE_MODE)
                ->setLocale(self::DEFAULT_LOCALE);
        return $setting;
    }

    /**
     * Replaces the sidebar entities in the setting.
     * @param Setting $setting
     * @param array<SidebarEntity>|SidebarEntity[] $sidebarEntities
     */
    public function replaceSidebarEntities(Setting $setting, array $sidebarEntities): void
    {
        $existingEntities = $this->mapSidebarEntities($setting->getSidebarEntities()->toArray());
        $newEntities = $this->mapSidebarEntities($sidebarEntities);

        $setting->getSidebarEntities()->clear();
        foreach ($newEntities as $key => $newEntity) {
            if (isset($existingEntities[$key])) {
                $persistedEntity = $existingEntities[$key];
            } else {
                $persistedEntity = $this->sidebarEntityRepository->createSidebarEntity(
                    $setting,
                    $newEntity->getType(),
                    $newEntity->getName()
                );
            }

            $this->hydrateSidebarEntity($newEntity, $persistedEntity);
            $setting->getSidebarEntities()->add($persistedEntity);
            unset($existingEntities[$key]);
        }

        // Remove entities which are no longer assigned to the setting.
        foreach ($existingEntities as $existingEntity) {
            $this->entityManager->remove($existingEntity);
        }
    }

    /**
     * Maps the sidebar entities to an associative array.
     * @param array<SidebarEntity>|SidebarEntity[] $sidebarEntities
     * @return array<string,SidebarEntity>|SidebarEntity[]
     */
    protected function mapSidebarEntities(array $sidebarEntities): array
    {
        $result = [];
        foreach ($sidebarEntities as $sidebarEntity) {
            $result["{$sidebarEntity->getType()}|{$sidebarEntity->getName()}"] = $sidebarEntity;
        }
        return $result;
    }

    /**
     * Hydrates the sidebar entity data from the source to the destination one.
     * @param SidebarEntity $source
     * @param SidebarEntity $destination
     */
    protected function hydrateSidebarEntity(SidebarEntity $source, SidebarEntity $destination): void
    {
        $destination->setLabel($source->getLabel())
                    ->setPinnedPosition($source->getPinnedPosition())
                    ->setLastViewTime($source->getLastViewTime());
    }
}
