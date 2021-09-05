<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The object representing a setting.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingData
{
    public string $combinationId = '';
    public string $combinationHash = '';
    public string $name = '';
    public string $locale = '';
    public string $recipeMode = '';
    public string $status = '';
    public bool $isTemporary = false;
}
