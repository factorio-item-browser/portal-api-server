<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server\Transfer;

use FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleData;
use FactorioItemBrowser\PortalApi\Server\Transfer\ModData;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use FactorioItemBrowserTestSerializer\PortalApi\Server\SerializerTestCase;

/**
 * The serializer test of the SettingDetailsData class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SettingDetailsDataTest extends SerializerTestCase
{
    private function getObject(): object
    {
        $mod1 = new ModData();
        $mod1->name = 'abc';
        $mod1->label = 'def';
        $mod1->author = 'ghi';
        $mod1->version = 'jkl';

        $mod2 = new ModData();
        $mod2->name = 'mno';
        $mod2->label = 'pqr';
        $mod2->author = 'stu';
        $mod2->version = 'vwx';

        $modIconsStyle = new IconsStyleData();
        $modIconsStyle->style = 'klm';

        $object = new SettingDetailsData();
        $object->combinationId = 'yza';
        $object->name = 'bcd';
        $object->status = 'klm';
        $object->isTemporary = true;
        $object->locale = 'efg';
        $object->recipeMode = 'hij';
        $object->mods = [$mod1, $mod2];
        $object->modIconsStyle = $modIconsStyle;
        return $object;
    }

    /**
     * @return array<mixed>
     */
    private function getData(): array
    {
        return [
            'combinationId' => 'yza',
            'name' => 'bcd',
            'status' => 'klm',
            'isTemporary' => true,
            'locale' => 'efg',
            'recipeMode' => 'hij',
            'mods' => [
                [
                    'name' => 'abc',
                    'label' => 'def',
                    'author' => 'ghi',
                    'version' => 'jkl',
                ],
                [
                    'name' => 'mno',
                    'label' => 'pqr',
                    'author' => 'stu',
                    'version' => 'vwx',
                ],
            ],
            'modIconsStyle' => [
                'processedEntities' => [],
                'style' => 'klm',
            ],
        ];
    }

    public function testSerialize(): void
    {
        $this->assertSerializedObject($this->getData(), $this->getObject());
    }
}
