<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Entity;

use DateTime;
use DateTimeInterface;
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
    private UuidInterface $id;

    /**
     * The user owning the setting.
     * @var User
     */
    private User $user;

    /**
     * The combination used for this setting.
     * @var Combination
     */
    private Combination $combination;

    /**
     * The name of the setting.
     * @var string
     */
    private string $name = '';

    /**
     * The locale used by this setting.
     * @var string
     */
    private string $locale = '';

    /**
     * The recipe mode used for this setting.
     * @var string
     */
    private string $recipeMode = '';

    /**
     * The time when the setting was last used.
     * @var DateTimeInterface
     */
    private DateTimeInterface $lastUsageTime;

    /**
     * Whether the setting has its data actually available.
     * @var bool
     */
    private bool $hasData = false;

    /**
     * Whether the setting is only temporary.
     * @var bool
     */
    private bool $isTemporary = false;

    /**
     * The sidebar entities of the setting.
     * @var Collection<int,SidebarEntity>
     */
    private Collection $sidebarEntities;

    public function __construct()
    {
        $this->lastUsageTime = new DateTime();
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
     * Sets the time when the setting was last used.
     * @param DateTimeInterface $lastUsageTime
     * @return $this
     */
    public function setLastUsageTime(DateTimeInterface $lastUsageTime): self
    {
        $this->lastUsageTime = $lastUsageTime;
        return $this;
    }

    /**
     * Returns the time when the setting was last used.
     * @return DateTimeInterface
     */
    public function getLastUsageTime(): DateTimeInterface
    {
        return $this->lastUsageTime;
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
     * Sets whether the setting is only temporary.
     * @param bool $isTemporary
     * @return $this
     */
    public function setIsTemporary(bool $isTemporary): self
    {
        $this->isTemporary = $isTemporary;
        return $this;
    }

    /**
     * Returns whether the setting is only temporary.
     * @return bool
     */
    public function getIsTemporary(): bool
    {
        return $this->isTemporary;
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
