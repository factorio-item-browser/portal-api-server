<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\UuidInterface;

/**
 * The class representing a setting of a user.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Setting
{
    /**
     * The ID of the setting.
     * @var UuidInterface
     */
    protected $id;

    /**
     * The user owning the setting.
     * @var User
     */
    protected $user;

    /**
     * The ID of the combination used for this setting.
     * @var UuidInterface
     */
    protected $combinationId;

    /**
     * The mod names used for this setting.
     * @var array|string[]
     */
    protected $modNames = [];

    /**
     * The locale used by this setting.
     * @var string
     */
    protected $locale = '';

    /**
     * The recipe mode used for this setting.
     * @var string
     */
    protected $recipeMode = '';

    /**
     * The API authorization token used for this setting.
     * @var string
     */
    protected $apiAuthorizationToken = '';

    /**
     * The sidebar entities of the setting.
     * @var Collection<int,SidebarEntity>
     */
    protected $sidebarEntities;

    /**
     * Initializes the entity.
     */
    public function __construct()
    {
        $this->sidebarEntities = new ArrayCollection();
    }

    /**
     * Sets the ID of the setting.
     * @param UuidInterface $id
     * @return $this
     */
    public function setId(UuidInterface $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Returns the ID of the setting.
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * Sets the user owning the setting.
     * @param User $user
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Returns the user owning the setting.
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Sets the ID of the combination used for this setting.
     * @param UuidInterface $combinationId
     * @return $this
     */
    public function setCombinationId(UuidInterface $combinationId): self
    {
        $this->combinationId = $combinationId;
        return $this;
    }

    /**
     * Returns the ID of the combination used for this setting.
     * @return UuidInterface
     */
    public function getCombinationId(): UuidInterface
    {
        return $this->combinationId;
    }

    /**
     * Sets the mod names used for this setting.
     * @param array|string[] $modNames
     * @return $this
     */
    public function setModNames(array $modNames): self
    {
        $this->modNames = $modNames;
        return $this;
    }

    /**
     * Returns the mod names used for this setting.
     * @return array|string[]
     */
    public function getModNames(): array
    {
        return $this->modNames;
    }

    /**
     * Sets the locale used by this setting.
     * @param string $locale
     * @return $this
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Returns the locale used by this setting.
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Sets the recipe mode used for this setting.
     * @param string $recipeMode
     * @return $this
     */
    public function setRecipeMode(string $recipeMode): self
    {
        $this->recipeMode = $recipeMode;
        return $this;
    }

    /**
     * Returns the recipe mode used for this setting.
     * @return string
     */
    public function getRecipeMode(): string
    {
        return $this->recipeMode;
    }

    /**
     * Sets the API authorization token used for this setting.
     * @param string $apiAuthorizationToken
     * @return $this
     */
    public function setApiAuthorizationToken(string $apiAuthorizationToken): self
    {
        $this->apiAuthorizationToken = $apiAuthorizationToken;
        return $this;
    }

    /**
     * Returns the API authorization token used for this setting.
     * @return string
     */
    public function getApiAuthorizationToken(): string
    {
        return $this->apiAuthorizationToken;
    }

    /**
     * Returns the sidebar entities of the setting.
     * @return Collection<int,SidebarEntity>
     */
    public function getSidebarEntities(): Collection
    {
        return $this->sidebarEntities;
    }
}
