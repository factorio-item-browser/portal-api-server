<?php

/**
 * The file providing the commands.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server;

use FactorioItemBrowser\PortalApi\Server\Constant\CommandName;

return [
    'commands' => [
        CommandName::CLEAN_SESSIONS => Command\CleanSessionsCommand::class,
    ],
];
