<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Mapper;

use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Mapper\SettingMapper;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingMetaData;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use stdClass;

/**
 * The PHPUnit test of the SettingMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Mapper\SettingMapper
 */
class SettingMapperTest extends TestCase
{
    /**
     * Provides the data for the supports test.
     * @return array<mixed>
     */
    public function provideSupports(): array
    {
        return [
            [new Setting(), new SettingMetaData(), true],
            [new Setting(), new SettingDetailsData(), true],
            [new Setting(), new stdClass(), false],
            [new stdClass(), new SettingMetaData(), false],
        ];
    }

    /**
     * Tests the supports method.
     * @param object $source
     * @param object $destination
     * @param bool $expectedResult
     * @covers ::supports
     * @dataProvider provideSupports
     */
    public function testSupports(object $source, object $destination, bool $expectedResult): void
    {
        $mapper = new SettingMapper();
        $result = $mapper->supports($source, $destination);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the map method.
     * @covers ::map
     */
    public function testMap(): void
    {
        $settingId = 'bc845964-3422-45ad-b8c1-819af3763667';

        $source = new Setting();
        $source->setId(Uuid::fromString($settingId));

        $expectedDestination = new SettingMetaData();
        $expectedDestination->setId($settingId)
                            ->setName('NOT YET IMPLEMENTED');


        $destination = new SettingMetaData();

        $mapper = new SettingMapper();
        $mapper->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}