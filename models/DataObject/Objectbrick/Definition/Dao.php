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

namespace OpenDxp\Model\DataObject\Objectbrick\Definition;

use OpenDxp\Db\Helper;
use OpenDxp\Model;
use OpenDxp\Model\DataObject;

/**
 * @internal
 *
 * @property \OpenDxp\Model\DataObject\Objectbrick\Definition $model
 */
class Dao extends Model\Dao\AbstractDao
{
    use DataObject\ClassDefinition\Helper\Dao;

    protected array $tableDefinitions = [];

    public function getTableName(DataObject\ClassDefinition $class, bool $query = false): string
    {
        if ($query) {
            return 'object_brick_query_' . $this->model->getKey() . '_' . $class->getId();
        }

        return 'object_brick_store_' . $this->model->getKey() . '_' . $class->getId();
    }

    public function getLocalizedTableName(DataObject\ClassDefinition $class, bool $query = false, string $language = 'en'): string
    {
        if ($query) {
            return 'object_brick_localized_query_' . $this->model->getKey() . '_' . $class->getId() . '_' . $language;
        }

        return 'object_brick_localized_' . $this->model->getKey() . '_' . $class->getId();
    }

    public function delete(DataObject\ClassDefinition $class): void
    {
        $table = $this->getTableName($class, false);
        $this->db->executeQuery(sprintf('DROP TABLE IF EXISTS `%s`', $table));

        $table = $this->getTableName($class, true);
        $this->db->executeQuery(sprintf('DROP TABLE IF EXISTS `%s`', $table));
    }

    public function createUpdateTable(DataObject\ClassDefinition $class): void
    {
        $tableStore = $this->getTableName($class, false);
        $tableQuery = $this->getTableName($class, true);

        $this->db->executeQuery(sprintf(
            "CREATE TABLE IF NOT EXISTS `%s` (
		  `id` int(11) UNSIGNED NOT NULL default '0',
          `fieldname` varchar(190) default '',
          PRIMARY KEY (`id`,`fieldname`),
          INDEX `id` (`id`),
          INDEX `fieldname` (`fieldname`),
          CONSTRAINT `%s` FOREIGN KEY (`id`) REFERENCES objects (`id`) ON DELETE CASCADE
		) DEFAULT CHARSET=utf8mb4;",
            $tableStore,
            self::getForeignKeyName($tableStore, 'id')
        ));

        $this->db->executeQuery(sprintf(
            "CREATE TABLE IF NOT EXISTS `%s` (
		  `id` int(11) UNSIGNED NOT NULL default '0',
          `fieldname` varchar(190) default '',
          PRIMARY KEY (`id`,`fieldname`),
          INDEX `id` (`id`),
          INDEX `fieldname` (`fieldname`),
          CONSTRAINT `%s` FOREIGN KEY (`id`) REFERENCES objects (`id`) ON DELETE CASCADE
		) DEFAULT CHARSET=utf8mb4;",
            $tableQuery,
            self::getForeignKeyName($tableQuery, 'id')
        ));

        $existingColumnsStore = $this->getValidTableColumns($tableStore, false); // no caching of table definition
        $columnsToRemoveStore = $existingColumnsStore;
        $existingColumnsQuery = $this->getValidTableColumns($tableQuery, false); // no caching of table definition
        $columnsToRemoveQuery = $existingColumnsQuery;

        $protectedColumnsStore = ['id', 'fieldname'];
        $protectedColumnsQuery = ['id', 'fieldname'];

        DataObject\ClassDefinition\Service::updateTableDefinitions($this->tableDefinitions, ([$tableStore, $tableQuery]));

        $this->removeIndices($tableStore, $columnsToRemoveStore, $protectedColumnsStore);
        $this->removeIndices($tableQuery, $columnsToRemoveQuery, $protectedColumnsQuery);

        foreach ($this->model->getFieldDefinitions() as $value) {
            $key = $value->getName();

            if ($value instanceof DataObject\ClassDefinition\Data\ResourcePersistenceAwareInterface) {
                // if a datafield requires more than one column in the datastore table => only for non-relation types
                if (!$value->isRelationType()) {
                    if (is_array($value->getColumnType())) {
                        foreach ($value->getColumnType() as $fkey => $fvalue) {
                            $this->addModifyColumn($tableStore, $key . '__' . $fkey, $fvalue, '', 'NULL');
                            $protectedColumnsStore[] = $key . '__' . $fkey;
                        }
                    } elseif ($value->getColumnType()) {
                        $this->addModifyColumn($tableStore, $key, $value->getColumnType(), '', 'NULL');
                        $protectedColumnsStore[] = $key;
                    }
                }

                $this->addIndexToField($value, $tableStore, 'getColumnType', true);
            }

            if ($value instanceof DataObject\ClassDefinition\Data\QueryResourcePersistenceAwareInterface) {
                // if a datafield requires more than one column in the query table
                if (is_array($value->getQueryColumnType())) {
                    foreach ($value->getQueryColumnType() as $fkey => $fvalue) {
                        $this->addModifyColumn($tableQuery, $key . '__' . $fkey, $fvalue, '', 'NULL');
                        $protectedColumnsQuery[] = $key . '__' . $fkey;
                    }
                } elseif ($value->getQueryColumnType()) {
                    $this->addModifyColumn($tableQuery, $key, $value->getQueryColumnType(), '', 'NULL');
                    $protectedColumnsQuery[] = $key;
                }

                $this->addIndexToField($value, $tableQuery, 'getQueryColumnType');
            }

            if ($value instanceof  DataObject\ClassDefinition\Data\Localizedfields) {
                $value->classSaved(
                    $class,
                    [
                        'context' => [
                            'containerType' => 'objectbrick',
                            'containerKey' => $this->model->getKey(),
                        ],
                    ]
                );
            }
        }

        $this->removeUnusedColumns($tableStore, $columnsToRemoveStore, $protectedColumnsStore);
        $this->removeUnusedColumns($tableQuery, $columnsToRemoveQuery, $protectedColumnsQuery);
    }

    public function classSaved(DataObject\ClassDefinition $classDefinition): void
    {
        $tableStore = $this->getTableName($classDefinition, false);
        $tableQuery = $this->getTableName($classDefinition, true);

        $this->handleEncryption($classDefinition, [$tableQuery, $tableStore]);
    }

    protected function removeIndices(string $table, array $columnsToRemove, array $protectedColumns): void
    {
        if ($columnsToRemove) {
            $indexPrefix = str_starts_with($table, 'object_brick_query_') ? 'p_index_' : 'u_index_';
            foreach ($columnsToRemove as $value) {
                if (!in_array(strtolower($value), $protectedColumns)) {
                    Helper::queryIgnoreError($this->db, sprintf('ALTER TABLE `%s` DROP INDEX `%s%s`;', $table, $indexPrefix, $value));
                }
            }

            $this->resetValidTableColumnsCache($table);
        }
    }
}
