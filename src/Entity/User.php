<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Entity;

use DateTime;
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
    private UuidInterface $id;

    /**
     * The time when the user last visited.
     * @var DateTimeInterface
     */
    private DateTimeInterface $lastVisitTime;

    /**
     * The settings of the user.
     * @var Collection<int,Setting>|Setting[]
     */
    private Collection $settings;

    /**
     * Initializes the entity.
     */
    public function __construct()
    {
        $this->lastVisitTime = new DateTime();
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
     * @return Collection<int,Setting>|Setting[]
     */
    public function getSettings(): Collection
    {
        return $this->settings;
    }

    /**
     * Returns the setting of the user having the specified combination id.
     * @param UuidInterface $combinationId
     * @return Setting|null
     */
    public function getSettingByCombinationId(UuidInterface $combinationId): ?Setting
    {
        foreach ($this->settings as $setting) {
            if ($combinationId->equals($setting->getCombination()->getId())) {
                return $setting;
            }
        }
        return null;
    }

    /**
     * Returns the setting last used by the user, excluding temporary settings.
     * @return Setting|null
     */
    public function getLastUsedSetting(): ?Setting
    {
        /* @var Setting|null $lastUsedSetting */
        $lastUsedSetting = null;
        $lastUsedTimestamp = 0;

        foreach ($this->settings as $setting) {
            if (!$setting->getIsTemporary() && $setting->getLastUsageTime()->getTimestamp() > $lastUsedTimestamp) {
                $lastUsedTimestamp = $setting->getLastUsageTime()->getTimestamp();
                $lastUsedSetting = $setting;
            }
        }
        return $lastUsedSetting;
    }
}
