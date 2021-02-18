<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Entity;

use DateTimeInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * The class representing a combination.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Combination
{
    /**
     * The id of the combination.
     * @var UuidInterface
     */
    private UuidInterface $id;

    /**
     * The mod names of the combination.
     * @var array<string>
     */
    private array $modNames = [];

    /**
     * The status of the combination.
     * @var string
     */
    private string $status = '';

    /**
     * The timestamp of export of the combination.
     * @var DateTimeInterface|null
     */
    private ?DateTimeInterface $exportTime = null;

    /**
     * The timestamp when the combination was last checked.
     * @var DateTimeInterface|null
     */
    private ?DateTimeInterface $lastCheckTime = null;

    /**
     * Sets the id of the combination.
     * @param UuidInterface $id
     * @return $this
     */
    public function setId(UuidInterface $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Returns the id of the combination.
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * Sets the mod names of the combination.
     * @param array<string> $modNames
     * @return $this
     */
    public function setModNames(array $modNames): self
    {
        $this->modNames = $modNames;
        return $this;
    }

    /**
     * Returns the mod names of the combination.
     * @return array<string>
     */
    public function getModNames(): array
    {
        return $this->modNames;
    }

    /**
     * Sets the status of the combination.
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Returns the status of the combination.
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Sets the timestamp of export of the combination.
     * @param DateTimeInterface|null $exportTime
     * @return $this
     */
    public function setExportTime(?DateTimeInterface $exportTime): self
    {
        $this->exportTime = $exportTime;
        return $this;
    }

    /**
     * Returns the timestamp of export of the combination.
     * @return DateTimeInterface|null
     */
    public function getExportTime(): ?DateTimeInterface
    {
        return $this->exportTime;
    }

    /**
     * Sets the timestamp when the combination was last checked.
     * @param DateTimeInterface|null $lastCheckTime
     * @return $this
     */
    public function setLastCheckTime(?DateTimeInterface $lastCheckTime): self
    {
        $this->lastCheckTime = $lastCheckTime;
        return $this;
    }

    /**
     * Returns the timestamp when the combination was last checked.
     * @return DateTimeInterface|null
     */
    public function getLastCheckTime(): ?DateTimeInterface
    {
        return $this->lastCheckTime;
    }
}
