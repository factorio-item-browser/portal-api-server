<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Transfer;

/**
 * The class representing entity names grouped by their types.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class NamesByTypes
{
    /** @var array<string, array<string>> */
    public array $values = [];

    public function add(string $type, string $name): self
    {
        $this->values[$type][] = $name;
        return $this;
    }

    public function has(string $type, string $name): bool
    {
        return in_array($name, $this->values[$type] ?? [], true);
    }
}
