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
     * The combination used for this setting.
     * @var Combination
     */
    protected $combination;

    /**
     * The name of the setting.
     * @var string
     */
    protected $name = '';

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
     * Whether the setting has its data actually available.
     * @var bool
     */
    protected $hasData = false;

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
     * Sets the combination used for this setting..
     * @param Combination $combination
     * @return $this
     */
    public function setCombination(Combination $combination): self
    {
        $this->combination = $combination;
        return $this;
    }

    /**
     * Returns the combination used for this setting.
     * @return Combination
     */
    public function getCombination(): Combination
    {
        return $this->combination;
    }

    /**
     * Sets the name of the setting.
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the setting.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
     * Sets whether the setting has its data actually available.
     * @param bool $hasData
     * @return $this
     */
    public function setHasData(bool $hasData): self
    {
        $this->hasData = $hasData;
        return $this;
    }

    /**
     * Returns whether the setting has its data actually available.
     * @return bool
     */
    public function getHasData(): bool
    {
        return $this->hasData;
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
