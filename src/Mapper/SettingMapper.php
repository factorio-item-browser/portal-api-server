<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\DynamicMapperInterface;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;

/**
 * The mapper of the settings.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements DynamicMapperInterface<Setting, SettingMetaData>
 */
class SettingMapper implements DynamicMapperInterface
{
    public function supports(object $source, object $destination): bool
    {
        return $source instanceof Setting && $destination instanceof SettingMetaData;
    }

    /**
     * @param Setting $source
     * @param SettingMetaData $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->combinationId = $source->getCombination()->getId()->toString();
        $destination->name = $source->getName();
        $destination->status = $source->getCombination()->getStatus();
        $destination->isTemporary = $source->getIsTemporary();

        if ($destination instanceof SettingDetailsData) {
            $destination->locale = $source->getLocale();
            $destination->recipeMode = $source->getRecipeMode();
        }
    }
}
