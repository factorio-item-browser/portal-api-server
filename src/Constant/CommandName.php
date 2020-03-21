<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Constant;

/**
 * The interface holding the names of the commands.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface CommandName
{
    /**
     * The command for cleaning old sessions.
     */
    public const CLEAN_SESSIONS = 'clean-sessions';
}
