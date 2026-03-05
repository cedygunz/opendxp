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

namespace OpenDxp\Model\DataObject\Fieldcollection\Definition;

use OpenDxp\Model;
use OpenDxp\Model\DataObject;

/**
 * @internal
 *
 * @property \OpenDxp\Model\DataObject\Fieldcollection\Definition $model
 */
class Dao extends Model\Dao\AbstractDao
{
    use DataObject\ClassDefinition\Helper\Dao;

    protected array $tableDefinitions = [];

    public function getTableName(DataObject\ClassDefinition $class): string
    {
        return 'object_collection_' . $this->model->getKey() . '_' . $class->getId();
    }

    public function getLocalizedTableName(DataObject\ClassDefinition $class): string
    {
        return 'object_collection_' . $this->model->getKey() . '_localized_' . $class->getId();
    }

    public function delete(DataObject\ClassDefinition $class): void
    {
        $table = $this->getTableName($class);
        $this->db->executeQuery(sprintf('DROP TABLE IF EXISTS `%s`', $table));
    }

    public function createUpdateTable(DataObject\ClassDefinition $class): void
    {
        $table = $this->getTableName($class);

        $this->db->executeQuery(sprintf(
            "CREATE TABLE IF NOT EXISTS `%s` (
		  `id` int(11) UNSIGNED NOT NULL default '0',
		  `index` int(11) default '0',
          `fieldname` varchar(190) default '',
          PRIMARY KEY (`id`,`index`,`fieldname`(190)),
          INDEX `index` (`index`),
          INDEX `fieldname` (`fieldname`),
          CONSTRAINT `%s` FOREIGN KEY (`id`) REFERENCES objects (`id`) ON DELETE CASCADE
		) DEFAULT CHARSET=utf8mb4;",
            $table,
            self::getForeignKeyName($table, 'id')
        ));

        $existingColumns = $this->getValidTableColumns($table, false); // no caching of table definition
        $columnsToRemove = $existingColumns;
        $protectedColums = ['id', 'index', 'fieldname'];

        DataObject\ClassDefinition\Service::updateTableDefinitions($this->tableDefinitions, ([$table]));

        foreach ($this->model->getFieldDefinitions() as $value) {
            $key = $value->getName();

            if ($value instanceof DataObject\ClassDefinition\Data\ResourcePersistenceAwareInterface) {
                if (is_array($value->getColumnType())) {
                    // if a datafield requires more than one field
                    foreach ($value->getColumnType() as $fkey => $fvalue) {
                        $this->addModifyColumn($table, $key . '__' . $fkey, $fvalue, '', 'NULL');
                        $protectedColums[] = $key . '__' . $fkey;
                    }
                } elseif ($value->getColumnType()) {
                    $this->addModifyColumn($table, $key, $value->getColumnType(), '', 'NULL');
                    $protectedColums[] = $key;
                }
                $this->addIndexToField($value, $table, 'getColumnType', true, false, true);
            }

            if ($value instanceof  DataObject\ClassDefinition\Data\Localizedfields) {
                $value->classSaved(
                    $class,
                    [
                        'context' => [
                            'containerType' => 'fieldcollection',
                            'containerKey' => $this->model->getKey(),
                        ],
                    ]
                );
            }
        }

        $this->removeIndices($table, $columnsToRemove, $protectedColums);
        $this->removeUnusedColumns($table, $columnsToRemove, $protectedColums);
        $this->tableDefinitions = [];
    }

    public function classSaved(DataObject\ClassDefinition $classDefinition): void
    {
        $this->handleEncryption($classDefinition, [$this->getTableName($classDefinition)]);
    }
}
