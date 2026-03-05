<?php

/**
 * OpenDXP
 *
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (https://pimcore.com)
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Model\DataObject\Classificationstore\KeyGroupRelation;

use OpenDxp\Db\Helper;
use OpenDxp\Model\Dao\AbstractDao;
use OpenDxp\Model\DataObject\Classificationstore;
use OpenDxp\Model\Exception\NotFoundException;
use OpenDxp\Tool\Serialize;

/**
 * @internal
 *
 * @property Classificationstore\KeyGroupRelation $model
 */
class Dao extends AbstractDao
{
    public const string TABLE_NAME_RELATIONS = 'classificationstore_relations';

    /**
     * @throws NotFoundException
     */
    public function getById(int $keyId, int $groupId): void
    {
        $this->model->setKeyId($keyId);
        $this->model->setGroupId($groupId);

        $data = $this->db->fetchAssociative(
            sprintf(
                'SELECT * FROM `%1$s` LEFT JOIN `%2$s` ON `%1$s`.`keyId` = `%2$s`.`id` WHERE `%1$s`.`keyId` = ? AND `%1$s`.`groupId` = ?',
                self::TABLE_NAME_RELATIONS,
                Classificationstore\KeyConfig\Dao::TABLE_NAME_KEYS
            ),
            [$this->model->getKeyId(), $this->model->getGroupId()]
        );

        if ($data) {
            $data['enabled'] = (bool)$data['enabled'];
            $data['mandatory'] = (bool)$data['mandatory'];
            $this->assignVariablesToModel($data);
        } else {
            throw new NotFoundException(sprintf(
                'KeyGroupRelation with keyId: %s and groupId: %s does not exist',
                $this->model->getKeyId(),
                $this->model->getGroupId()
            ));
        }
    }

    public function save(): void
    {
        $this->update();
    }

    /**
     * Deletes object from database
     */
    public function delete(): void
    {
        $this->db->delete(self::TABLE_NAME_RELATIONS, [
            'keyId' => $this->model->getKeyId(),
            'groupId' => $this->model->getGroupId(),
        ]);
    }

    public function update(): void
    {
        $type = $this->model->getObjectVars();
        $validTableColumns = $this->getValidTableColumns(self::TABLE_NAME_RELATIONS);
        $data = [];

        foreach ($type as $key => $value) {
            if (in_array($key, $validTableColumns)) {
                if (is_bool($value)) {
                    $value = (int) $value;
                }
                if (is_array($value) || is_object($value)) {
                    $value = Serialize::serialize($value);
                }

                $data[$key] = $value;
            }
        }

        Helper::upsert($this->db, self::TABLE_NAME_RELATIONS, $data, $this->getPrimaryKey(self::TABLE_NAME_RELATIONS));
    }
}
