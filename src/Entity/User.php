<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\UuidInterface;

/**
 * The class representing a user in the database.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class User
{
    /**
     * The ID of the user.
     * @var UuidInterface
     */
    protected $id;

    /**
     * The locale used by the user.
     * @var string
     */
    protected $locale = '';

    /**
     * The time when the user last visited.
     * @var DateTimeInterface
     */
    protected $lastVisitTime;

    /**
     * The settings of the user.
     * @var Collection<int,Setting>
     */
    protected $settings;

    /**
     * The setting currently active for the user.
     * @var Setting|null
     */
    protected $currentSetting;

    /**
     * Initializes the entity.
     */
    public function __construct()
    {
        $this->settings = new ArrayCollection();
    }

    /**
     * Sets the ID of the user.
     * @param UuidInterface $id
     * @return $this
     */
    public function setId(UuidInterface $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Returns the ID of the user.
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * Sets the locale used by the user.
     * @param string $locale
     * @return $this
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Returns the locale used by the user.
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Sets the time when the user last visited.
     * @param DateTimeInterface $lastVisitTime
     * @return $this
     */
    public function setLastVisitTime(DateTimeInterface $lastVisitTime): self
    {
        $this->lastVisitTime = $lastVisitTime;
        return $this;
    }

    /**
     * Returns the time when the user last visited.
     * @return DateTimeInterface
     */
    public function getLastVisitTime(): DateTimeInterface
    {
        return $this->lastVisitTime;
    }

    /**
     * Returns the settings of the user.
     * @return Collection<int,Setting>
     */
    public function getSettings(): Collection
    {
        return $this->settings;
    }

    /**
     * Returns the setting currently active for the user.
     * @return Setting|null
     */
    public function getCurrentSetting(): ?Setting
    {
        return $this->currentSetting;
    }

    /**
     * Sets the setting currently active for the user.
     * @param Setting|null $currentSetting
     * @return $this
     */
    public function setCurrentSetting(?Setting $currentSetting): self
    {
        $this->currentSetting = $currentSetting;
        return $this;
    }
}
