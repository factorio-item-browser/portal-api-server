<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The object representing the style for entity icons.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconsStyleData
{
    public NamesByTypes $processedEntities;
    public string $style = '';

    public function __construct()
    {
        $this->processedEntities = new NamesByTypes();
    }
}
