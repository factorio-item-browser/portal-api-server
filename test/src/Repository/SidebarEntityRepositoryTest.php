<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Repository;

use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\SidebarEntity;
use FactorioItemBrowser\PortalApi\Server\Repository\SidebarEntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the SidebarEntityRepository class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Repository\SidebarEntityRepository
 */
class SidebarEntityRepositoryTest extends TestCase
{
    /**
     * Tests the createSidebarEntity method.
     * @covers ::createSidebarEntity
     */
    public function testCreateSidebarEntity(): void
    {
        $type = 'abc';
        $name = 'def';

        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);

        $expectedResult = new SidebarEntity();
        $expectedResult->setSetting($setting)
                       ->setType($type)
                       ->setName($name);

        $repository = new SidebarEntityRepository();
        $result = $repository->createSidebarEntity($setting, $type, $name);

        $this->assertEquals($expectedResult, $result);
    }
}
