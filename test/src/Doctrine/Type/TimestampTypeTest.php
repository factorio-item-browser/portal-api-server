<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FactorioItemBrowser\PortalApi\Server\Doctrine\Type\TimestampType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the TimestampType class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Doctrine\Type\TimestampType
 */
class TimestampTypeTest extends TestCase
{
    /**
     * Tests the getName method.
     * @covers ::getName
     */
    public function testGetName(): void
    {
        $expectedResult = TimestampType::NAME;

        /* @var TimestampType&MockObject $type */
        $type = $this->getMockBuilder(TimestampType::class)
                     ->onlyMethods(['__construct'])
                     ->disableOriginalConstructor()
                     ->getMock();
        $result = $type->getName();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getSQLDeclaration method.
     * @covers ::getSQLDeclaration
     */
    public function testGetSQLDeclaration(): void
    {
        $fieldDeclaration = ['abc'];
        $expectedResult = 'TIMESTAMP';

        /* @var AbstractPlatform&MockObject $platform */
        $platform = $this->createMock(AbstractPlatform::class);

        /* @var TimestampType&MockObject $type */
        $type = $this->getMockBuilder(TimestampType::class)
                     ->onlyMethods(['__construct'])
                     ->disableOriginalConstructor()
                     ->getMock();
        $result = $type->getSQLDeclaration($fieldDeclaration, $platform);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the requiresSQLCommentHint method.
     * @covers ::requiresSQLCommentHint
     */
    public function testRequiresSQLCommentHint(): void
    {
        /* @var AbstractPlatform&MockObject $platform */
        $platform = $this->createMock(AbstractPlatform::class);

        /* @var TimestampType&MockObject $type */
        $type = $this->getMockBuilder(TimestampType::class)
                     ->onlyMethods(['__construct'])
                     ->disableOriginalConstructor()
                     ->getMock();
        $result = $type->requiresSQLCommentHint($platform);

        $this->assertTrue($result);
    }
}
