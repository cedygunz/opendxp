<?php
declare(strict_types=1);

/**
 * OpenDXP
 *
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) OpenDXP (https://www.opendxp.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Tests\Model\DataType;

use OpenDxp\Model\DataObject;
use OpenDxp\Model\DataObject\Fieldcollection;
use OpenDxp\Model\DataObject\Fieldcollection\Definition;
use OpenDxp\Tests\Support\Test\ModelTestCase;
use OpenDxp\Tests\Support\Util\TestHelper;

/**
 * @group model.dataobject.classdefinition.data.fieldcollections
 */
class FieldcollectionsTest extends ModelTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        TestHelper::cleanUp();
    }

    public function tearDown(): void
    {
        TestHelper::cleanUp();
        parent::tearDown();
    }

    protected function setUpTestClasses(): void
    {
        $this->tester->setupOpenDxpClass_RelationTest();
        $this->tester->setupFieldcollection_Unittestfieldcollection();
    }

    private function markFieldinput1Invisible(): void
    {
        $collectionDef = Definition::getByKey('unittestfieldcollection');
        $fieldDefinitions = $collectionDef->getFieldDefinitions();
        $fieldDefinitions['fieldinput1']->setInvisible(true);
        $collectionDef->setFieldDefinitions($fieldDefinitions);
        $collectionDef->save();
    }

    private function buildEditmodeData(?string $fieldinput1Value, bool $submitFieldinput1): array
    {
        $data = [
            'fieldinput2' => 'unchanged',
        ];

        if ($submitFieldinput1) {
            $data['fieldinput1'] = $fieldinput1Value;
        }

        return [[
            'data' => $data,
            'type' => 'unittestfieldcollection',
            'oIndex' => 0,
            'title' => 'unittestfieldcollection',
        ]];
    }

    public function testSubmittedValueForInvisibleField(): void
    {
        $object = TestHelper::createEmptyObject();

        $item = new Fieldcollection\Data\Unittestfieldcollection();
        $item->setFieldinput1('persisted value');
        $object->setFieldcollection(new Fieldcollection([$item]));
        $object->save();

        // Field is exposed as editable via the Main (Admin Mode) layout, even though it's invisible in the default layout
        $this->markFieldinput1Invisible();

        $object = DataObject::getById($object->getId(), ['force' => true]);
        $fcFieldDefinition = $object->getClass()->getFieldDefinition('fieldcollection');

        $editmodeData = $this->buildEditmodeData('edited value', true);
        $result = $fcFieldDefinition->getDataFromEditmode($editmodeData, $object);

        $this->assertEquals('edited value', $result->get(0)->getFieldinput1());
    }

    public function testInvisibleFieldWithoutSubmittedValue(): void
    {
        $object = TestHelper::createEmptyObject();

        $item = new Fieldcollection\Data\Unittestfieldcollection();
        $item->setFieldinput1('persisted value');
        $object->setFieldcollection(new Fieldcollection([$item]));
        $object->save();

        $this->markFieldinput1Invisible();

        $object = DataObject::getById($object->getId(), ['force' => true]);
        $fcFieldDefinition = $object->getClass()->getFieldDefinition('fieldcollection');

        $editmodeData = $this->buildEditmodeData(null, false);
        $result = $fcFieldDefinition->getDataFromEditmode($editmodeData, $object);

        $this->assertEquals('persisted value', $result->get(0)->getFieldinput1());
    }
}
