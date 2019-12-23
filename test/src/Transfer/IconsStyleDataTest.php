<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleData;
use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the IconsStyleData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleData
 */
class IconsStyleDataTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $transfer = new IconsStyleData();

        $this->assertInstanceOf(NamesByTypes::class, $this->extractProperty($transfer, 'processedEntities'));
    }

    /**
     * Tests the setting and getting the processed entities.
     * @covers ::getProcessedEntities
     * @covers ::setProcessedEntities
     */
    public function testSetAndGetProcessedEntities(): void
    {
        /* @var NamesByTypes&MockObject $processedEntities */
        $processedEntities = $this->createMock(NamesByTypes::class);
        $transfer = new IconsStyleData();

        $this->assertSame($transfer, $transfer->setProcessedEntities($processedEntities));
        $this->assertSame($processedEntities, $transfer->getProcessedEntities());
    }

    /**
     * Tests the setting and getting the style.
     * @covers ::getStyle
     * @covers ::setStyle
     */
    public function testSetAndGetStyle(): void
    {
        $style = 'abc';
        $transfer = new IconsStyleData();

        $this->assertSame($transfer, $transfer->setStyle($style));
        $this->assertSame($style, $transfer->getStyle());
    }
}
