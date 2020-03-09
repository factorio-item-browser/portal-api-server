<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use FactorioItemBrowser\Api\Client\Entity\Mod;
use FactorioItemBrowser\PortalApi\Server\Mapper\ModMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\ModData;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ModMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Mapper\ModMapper
 */
class ModMapperTest extends TestCase
{
    /**
     * Tests the getSupportedSourceClass method.
     * @covers ::getSupportedSourceClass
     */
    public function testGetSupportedSourceClass(): void
    {
        $expectedResult = Mod::class;

        $mapper = new ModMapper();
        $result = $mapper->getSupportedSourceClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getSupportedDestinationClass method.
     * @covers ::getSupportedDestinationClass
     */
    public function testGetSupportedDestinationClass(): void
    {
        $expectedResult = ModData::class;

        $mapper = new ModMapper();
        $result = $mapper->getSupportedDestinationClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the map method.
     * @covers ::map
     */
    public function testMap(): void
    {
        $source = new Mod();
        $source->setName('abc')
               ->setLabel('def')
               ->setAuthor('ghi')
               ->setVersion('1.2.3');

        $expectedDestination = new ModData();
        $expectedDestination->setName('abc')
                            ->setLabel('def')
                            ->setAuthor('ghi')
                            ->setVersion('1.2.3');

        $destination = new ModData();

        $mapper = new ModMapper();
        $mapper->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
