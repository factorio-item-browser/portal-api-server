<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

use DateTimeInterface;

/**
 * The class representing the status of a setting.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingStatusData
{
    /**
     * The export status of the combination.
     * @var string
     */
    protected $status = '';

    /**
     * The timestamp when the data was exported from Factorio.
     * @var DateTimeInterface|null
     */
    protected $exportTime;

    /**
     * Sets the export status of the combination.
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Returns the export status of the combination.
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Sets the timestamp when the data was exported from Factorio.
     * @param DateTimeInterface|null $exportTime
     * @return $this
     */
    public function setExportTime(?DateTimeInterface $exportTime): self
    {
        $this->exportTime = $exportTime;
        return $this;
    }

    /**
     * Returns the timestamp when the data was exported from Factorio.
     * @return DateTimeInterface|null
     */
    public function getExportTime(): ?DateTimeInterface
    {
        return $this->exportTime;
    }
}
