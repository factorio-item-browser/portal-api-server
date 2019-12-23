<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the NamesByTypes class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes
 */
class NamesByTypesTest extends TestCase
{
    /**
     * Tests the setting, adding and getting the values.
     * @covers ::addValue
     * @covers ::getValues
     * @covers ::setValues
     */
    public function testSetAddAndGetValues(): void
    {
        $values = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno'],
        ];
        $expectedValues1 = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno', 'pqr'],
        ];
        $expectedValues2 = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno', 'pqr'],
            'stu' => ['vwx'],
        ];

        $transfer = new NamesByTypes();

        $this->assertSame($transfer, $transfer->setValues($values));
        $this->assertSame($values, $transfer->getValues());

        $this->assertSame($transfer, $transfer->addValue('jkl', 'pqr'));
        $this->assertSame($expectedValues1, $transfer->getValues());

        $this->assertSame($transfer, $transfer->addValue('stu', 'vwx'));
        $this->assertSame($expectedValues2, $transfer->getValues());
    }
}
