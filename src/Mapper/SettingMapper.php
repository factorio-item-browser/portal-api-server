<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingData;

/**
 * The mapper of the settings.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<Setting, SettingData>
 */
class SettingMapper implements StaticMapperInterface
{
    public function __construct(
        public string $scriptVersion,
    ) {
    }

    public function getSupportedSourceClass(): string
    {
        return Setting::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return SettingData::class;
    }

    /**
     * @param Setting $source
     * @param SettingData $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->combinationId = $source->getCombination()->getId()->toString();
        $destination->combinationHash = $this->calculateCombinationHash($source->getCombination());
        $destination->name = $source->getName();
        $destination->locale = $source->getLocale();
        $destination->recipeMode = $source->getRecipeMode();
        $destination->status = $source->getCombination()->getStatus();
        $destination->isTemporary = $source->getIsTemporary();
    }

    private function calculateCombinationHash(Combination $combination): string
    {
        return substr(hash('md5', (string) json_encode([
            $combination->getExportTime()?->getTimestamp(),
            $this->scriptVersion,
        ])), 0, 16);
    }
}
