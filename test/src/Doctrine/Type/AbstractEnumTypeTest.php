<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FactorioItemBrowser\PortalApi\Server\Doctrine\Type\AbstractEnumType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the AbstractEnumType class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Doctrine\Type\AbstractEnumType
 */
class AbstractEnumTypeTest extends TestCase
{
    /**
     * Tests the getSQLDeclaration method.
     * @covers ::getSQLDeclaration
     */
    public function testGetSQLDeclaration(): void
    {
        $expectedResult = 'ENUM("foo","bar")';
        $fieldDeclaration = [];

        /* @var AbstractPlatform&MockObject $platform */
        $platform = $this->createMock(AbstractPlatform::class);
        $platform->expects($this->any())
                 ->method('quoteStringLiteral')
                 ->with($this->isType('string'))
                 ->willReturnCallback(function (string $value): string {
                     return sprintf('"%s"', $value);
                 });

        /* @var AbstractEnumType&MockObject $type */
        $type = $this->getMockBuilder(AbstractEnumType::class)
                     ->disableOriginalConstructor()
                     ->getMockForAbstractClass();

        $result = $type->getSQLDeclaration($fieldDeclaration, $platform);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getName method.
     * @covers ::getName
     */
    public function testGetName(): void
    {
        $expectedResult = 'enum';

        /* @var AbstractEnumType&MockObject $type */
        $type = $this->getMockBuilder(AbstractEnumType::class)
                     ->disableOriginalConstructor()
                     ->getMockForAbstractClass();

        $result = $type->getName();

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

        /* @var AbstractEnumType&MockObject $type */
        $type = $this->getMockBuilder(AbstractEnumType::class)
                     ->disableOriginalConstructor()
                     ->getMockForAbstractClass();

        $result = $type->requiresSQLCommentHint($platform);

        $this->assertTrue($result);
    }
}
