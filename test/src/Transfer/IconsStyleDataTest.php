<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleData;
use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the IconsStyleData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleData
 */
class IconsStyleDataTest extends TestCase
{
    public function testConstruct(): void
    {
        $instance = new IconsStyleData();

        $this->assertEquals(new NamesByTypes(), $instance->processedEntities);
    }
}
