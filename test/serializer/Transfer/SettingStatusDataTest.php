<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use DateTime;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingStatusData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the SettingStatusData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingStatusDataTest extends SerializerTestCase
{
    /**
     * Returns the object to test on.
     * @return object
     * @throws Exception
     */
    protected function getObject(): object
    {
        $object = new SettingStatusData();
        $object->setStatus('abc')
               ->setExportTime(new DateTime('2038-01-19 03:14:07'));

        return $object;
    }

    /**
     * Returns the data to test on.
     * @return array<mixed>
     */
    protected function getData(): array
    {
        return [
            'status' => 'abc',
            'exportTime' => '2038-01-19T03:14:07.000+00:00',
        ];
    }

    /**
     * Tests the serialization.
     * @throws Exception
     */
    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
