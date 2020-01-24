<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Helper;

use FactorioItemBrowser\Api\Client\Entity\Recipe;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\PortalApi\Server\Constant\RecipeMode;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;

/**
 * The class helping with selecting the recipes to be shown in the frontend.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeSelector
{
    /**
     * The current user setting.
     * @var Setting
     */
    protected $currentSetting;

    /**
     * Initializes the recipe selector.
     * @param Setting $currentSetting
     */
    public function __construct(Setting $currentSetting)
    {
        $this->currentSetting = $currentSetting;
    }

    /**
     * Selects the recipe versions to actually show in the frontend.
     * @param Recipe $recipe
     * @return array<Recipe>
     */
    public function select(Recipe $recipe): array
    {
        $normalRecipe = $recipe;
        $expensiveRecipe = null;
        if ($recipe instanceof RecipeWithExpensiveVersion) {
            $expensiveRecipe = $recipe->getExpensiveVersion();
        }

        $preferredMode = $this->currentSetting->getRecipeMode();
        if ($preferredMode === RecipeMode::HYBRID) {
            return array_values(array_filter([$normalRecipe, $expensiveRecipe]));
        }
        if ($preferredMode === RecipeMode::EXPENSIVE && $expensiveRecipe !== null) {
            $expensiveRecipe = clone $expensiveRecipe;
            $expensiveRecipe->setMode(RecipeMode::NORMAL); // Consider the expensive recipe as a normal one.
            return [$expensiveRecipe];
        }
        return [$normalRecipe];
    }

    /**
     * Selects from an array of recipes all versions to actually show in the frontend.
     * @param array<Recipe> $recipes
     * @return array<Recipe>
     */
    public function selectArray(array $recipes): array
    {
        $result = [];
        foreach ($recipes as $recipe) {
            $result = array_merge($result, $this->select($recipe));
        }
        return $result;
    }
}
