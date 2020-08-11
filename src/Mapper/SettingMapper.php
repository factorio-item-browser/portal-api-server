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
 */
class SettingMapper implements DynamicMapperInterface
{
    /**
     * Returns whether the mapper supports the combination of source and destination object.
     * @param object $source
     * @param object $destination
     * @return bool
     */
    public function supports($source, $destination): bool
    {
        return $source instanceof Setting && $destination instanceof SettingMetaData;
    }

    /**
     * Maps the source object to the destination one.
     * @param Setting $source
     * @param SettingMetaData $destination
     */
    public function map($source, $destination): void
    {
        $destination->setCombinationId($source->getCombination()->getId()->toString())
                    ->setName($source->getName())
                    ->setStatus($source->getCombination()->getStatus())
                    ->setIsTemporary($source->getIsTemporary());

        if ($destination instanceof SettingDetailsData) {
            $destination->setLocale($source->getLocale())
                        ->setRecipeMode($source->getRecipeMode());
        }
    }
}
