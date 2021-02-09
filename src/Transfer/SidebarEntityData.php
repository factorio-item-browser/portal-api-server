<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

use DateTime;
use DateTimeInterface;

/**
 * The object representing an entity of the sidebar.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SidebarEntityData
{
    public string $type = '';
    public string $name = '';
    public string $label = '';
    public int $pinnedPosition = 0;
    public DateTimeInterface $lastViewTime;

    public function __construct()
    {
        $this->lastViewTime = new DateTime();
    }
}
