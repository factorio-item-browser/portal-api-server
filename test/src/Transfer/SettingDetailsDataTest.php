<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleData;
use FactorioItemBrowser\PortalApi\Server\Transfer\ModData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the SettingDetailsData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData
 */
class SettingDetailsDataTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $transfer = new SettingDetailsData();

        $this->assertSame('', $transfer->getRecipeMode());
        $this->assertSame('', $transfer->getLocale());
        $this->assertSame([], $transfer->getMods());

        // Asserted through type-hint
        $transfer->getModIconsStyle();
    }

    /**
     * Tests the setting and getting the locale.
     * @covers ::getLocale
     * @covers ::setLocale
     */
    public function testSetAndGetLocale(): void
    {
        $locale = 'abc';
        $transfer = new SettingDetailsData();

        $this->assertSame($transfer, $transfer->setLocale($locale));
        $this->assertSame($locale, $transfer->getLocale());
    }

    /**
     * Tests the setting and getting the recipe mode.
     * @covers ::getRecipeMode
     * @covers ::setRecipeMode
     */
    public function testSetAndGetRecipeMode(): void
    {
        $recipeMode = 'abc';
        $transfer = new SettingDetailsData();

        $this->assertSame($transfer, $transfer->setRecipeMode($recipeMode));
        $this->assertSame($recipeMode, $transfer->getRecipeMode());
    }

    /**
     * Tests the setting and getting the mods.
     * @covers ::getMods
     * @covers ::setMods
     */
    public function testSetAndGetMods(): void
    {
        $mods = [
            $this->createMock(ModData::class),
            $this->createMock(ModData::class),
        ];
        $transfer = new SettingDetailsData();

        $this->assertSame($transfer, $transfer->setMods($mods));
        $this->assertSame($mods, $transfer->getMods());
    }

    /**
     * Tests the setting and getting the mod icons style.
     * @covers ::getModIconsStyle
     * @covers ::setModIconsStyle
     */
    public function testSetAndGetModIconsStyle(): void
    {
        /* @var IconsStyleData&MockObject $modIconsStyle */
        $modIconsStyle = $this->createMock(IconsStyleData::class);
        $transfer = new SettingDetailsData();

        $this->assertSame($transfer, $transfer->setModIconsStyle($modIconsStyle));
        $this->assertSame($modIconsStyle, $transfer->getModIconsStyle());
    }
}
