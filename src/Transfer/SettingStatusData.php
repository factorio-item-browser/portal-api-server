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
    public string $status = '';
    public ?DateTimeInterface $exportTime = null;
    public ?SettingDetailsData $existingSetting = null;
}
