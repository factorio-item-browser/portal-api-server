<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The object representing the request of icon styles.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconsStyleRequestData
{
    public string $cssSelector = '';
    public NamesByTypes $entities;

    public function __construct()
    {
        $this->entities = new NamesByTypes();
    }
}
