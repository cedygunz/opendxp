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

namespace OpenDxp\Model\DataObject\Classificationstore\KeyGroupRelation\Listing;

use OpenDxp\Model;
use OpenDxp\Model\DataObject;

/**
 * @internal
 *
 * @property \OpenDxp\Model\DataObject\Classificationstore\KeyGroupRelation\Listing $model
 */
class Dao extends Model\Listing\Dao\AbstractDao
{
    /**
     * Loads a list of Classificationstore group configs for the specified parameters, returns an array of config elements
     */
    public function load(): array
    {
        $sql = 'SELECT ' . DataObject\Classificationstore\KeyGroupRelation\Dao::TABLE_NAME_RELATIONS . '.*,'
            . DataObject\Classificationstore\KeyConfig\Dao::TABLE_NAME_KEYS . '.*';

        $resourceGroupName = $this->model->getResolveGroupName();

        if ($resourceGroupName) {
            $sql .= ', ' . DataObject\Classificationstore\GroupConfig\Dao::TABLE_NAME_GROUPS . '.name as groupName';
        }

        $sql .= $this->getFrom() . $this->getWhere() . $this->getOrder() . $this->getOffsetLimit();
        $data = $this->db->fetchAllAssociative($sql, $this->model->getConditionVariables(), $this->model->getConditionVariableTypes());

        $configData = [];
        foreach ($data as $dataItem) {
            $entry = new DataObject\Classificationstore\KeyGroupRelation();
            $resource = $entry->getDao();
            $dataItem['enabled'] = (bool)$dataItem['enabled'];
            $dataItem['mandatory'] = (bool)$dataItem['mandatory'];

            $definition = json_decode($dataItem['definition'], true);
            $definition['mandatory'] = $dataItem['mandatory'];
            $dataItem['definition'] = json_encode($definition);

            $resource->assignVariablesToModel($dataItem);

            $configData[] = $entry;
        }

        $this->model->setList($configData);

        return $configData;
    }

    public function getDataArray(): array
    {
        return $this->db->fetchAllAssociative(
            'SELECT *' . $this->getFrom() . $this->getWhere() . $this->getOrder() . $this->getOffsetLimit(),
            $this->model->getConditionVariables(),
            $this->model->getConditionVariableTypes()
        );
    }

    public function getTotalCount(): int
    {
        return (int) $this->db->fetchOne(
            'SELECT COUNT(*)' . $this->getFrom() . $this->getWhere(),
            $this->model->getConditionVariables(),
            $this->model->getConditionVariableTypes()
        );
    }

    private function getWhere(): string
    {
        $where = parent::getCondition();
        if ($where) {
            $where .= ' AND ';
        } else {
            $where = ' WHERE ';
        }
        $where .= DataObject\Classificationstore\KeyGroupRelation\Dao::TABLE_NAME_RELATIONS
            . '.keyId = ' . DataObject\Classificationstore\KeyConfig\Dao::TABLE_NAME_KEYS . '.id';

        $resourceGroupName = $this->model->getResolveGroupName();

        if ($resourceGroupName) {
            $where .= ' and ' . DataObject\Classificationstore\GroupConfig\Dao::TABLE_NAME_GROUPS . '.id = '
                . DataObject\Classificationstore\KeyGroupRelation\Dao::TABLE_NAME_RELATIONS . '.groupId';
        }

        return $where;
    }

    private function getFrom(): string
    {
        $from = ' FROM ' . DataObject\Classificationstore\KeyGroupRelation\Dao::TABLE_NAME_RELATIONS
            . ',' . DataObject\Classificationstore\KeyConfig\Dao::TABLE_NAME_KEYS;
        $resourceGroupName = $this->model->getResolveGroupName();

        if ($resourceGroupName) {
            $from .= ', ' . DataObject\Classificationstore\GroupConfig\Dao::TABLE_NAME_GROUPS;
        }

        return $from;
    }
}
