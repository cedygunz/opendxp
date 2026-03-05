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
 * @copyright  Copyright (c) Pimcore GmbH (https://pimcore.com)
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Model\DataObject\Concrete\Dao;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Exception;
use OpenDxp\Db\Helper;
use OpenDxp\Model\DataObject;

/**
 * @internal
 */
class InheritanceHelper
{
    public const string STORE_TABLE = 'object_store_';
    public const string QUERY_TABLE = 'object_query_';
    public const string RELATION_TABLE = 'object_relations_';
    public const string ID_FIELD = 'oo_id';
    public const string DEFAULT_QUERY_ID_COLUMN = 'ooo_id';

    protected Connection $db;

    protected array $fields = [];

    protected array $relations = [];

    protected array $fieldIds = [];

    protected array $deletionFieldIds = [];

    protected array $fieldDefinitions = [];

    protected static bool $useRuntimeCache = false;

    protected bool $childFound = false;

    protected static array $runtimeCache = [];

    protected ?string $storetable = null;

    protected ?string $querytable = null;

    protected ?string $relationtable = null;

    protected ?string $idField = null;

    protected ?string $queryIdField = null;

    public function __construct(
        protected string $classId,
        ?string $idField = null,
        ?string $storetable = null,
        ?string $querytable = null,
        ?string $relationtable = null,
        ?string $queryIdField = null
    ) {
        $this->db = \OpenDxp\Db::get();
        $this->storetable = $storetable ?? self::STORE_TABLE . $this->classId;
        $this->querytable = $querytable ?? self::QUERY_TABLE . $this->classId;
        $this->relationtable = $relationtable ?? self::RELATION_TABLE . $this->classId;
        $this->idField = $idField ?? self::ID_FIELD;
        $this->queryIdField = $queryIdField ?? self::DEFAULT_QUERY_ID_COLUMN;
    }

    /**
     * Enable or disable the runtime cache. Default value is off.
     */
    public static function setUseRuntimeCache(bool $value): void
    {
        self::$useRuntimeCache = $value;
    }

    /**
     * clear the runtime cache
     */
    public static function clearRuntimeCache(): void
    {
        self::$runtimeCache = [];
    }

    public function resetFieldsToCheck(): void
    {
        $this->fields = [];
        $this->relations = [];
        $this->fieldIds = [];
        $this->deletionFieldIds = [];
        $this->fieldDefinitions = [];
        $this->childFound = false;
    }

    public function addFieldToCheck(string $fieldname, DataObject\ClassDefinition\Data $fieldDefinition): void
    {
        $this->fields[$fieldname] = $fieldname;
        $this->fieldIds[$fieldname] = [];
        $this->fieldDefinitions[$fieldname] = $fieldDefinition;
    }

    public function addRelationToCheck(string $fieldname, DataObject\ClassDefinition\Data $fieldDefinition, ?array $queryfields = null): void
    {
        $this->relations[$fieldname] = $queryfields ?? $fieldname;

        $this->fieldIds[$fieldname] = [];
        $this->fieldDefinitions[$fieldname] = $fieldDefinition;
    }

    /**
     * @throws Exception
     */
    public function doUpdate(int $oo_id, bool $createMissingChildrenRows = false, array $params = []): void
    {
        if ($this->fields === [] && $this->relations === [] && !$createMissingChildrenRows) {
            return;
        }

        // only build the tree if there are fields to check
        if ($this->fields !== [] || $this->relations !== []) {
            $fields = implode('`,`', $this->fields);
            if (!empty($fields)) {
                $fields = ', `' . $fields . '`';
            }

            $result = $this->db->fetchAssociative(
                sprintf('SELECT %s AS id%s FROM %s WHERE %s = ?', $this->idField, $fields, $this->storetable, $this->idField),
                [$oo_id]
            );

            $o = [
                'id' => $result['id'],
                'values' => $result,
            ];

            $o['children'] = $this->buildTree($result['id'], $fields, null, $params);

            foreach ($this->fields as $fieldname) {
                foreach ($o['children'] as $c) {
                    $this->getIdsToUpdateForValuefields($c, $fieldname);
                }

                $this->updateQueryTable($oo_id, $this->fieldIds[$fieldname], $fieldname);
                // not needed anymore
                unset($this->fieldIds[$fieldname]);
            }

            foreach ($this->relations as $fieldname => $fields) {
                foreach ($o['children'] as $c) {
                    $this->getIdsToUpdateForRelationfields($c, $fieldname, $params);
                }

                if (is_array($fields)) {
                    foreach ($fields as $f) {
                        $this->updateQueryTable($oo_id, $this->fieldIds[$fieldname], $f);
                    }
                } else {
                    $this->updateQueryTable($oo_id, $this->fieldIds[$fieldname], $fieldname);
                }
                // not needed anymore
                unset($this->fieldIds[$fieldname]);
            }
        }

        // check for missing entries which can occur in object bricks and localized fields
        // this happens especially in the following case:
        // parent object has no brick, add child to parent, add brick to parent & click save
        // without this code there will not be an entry in the query table for the child object
        // if we have a tree (which is the case if either fields or relations is configured) then
        // rely on the childFound flag
        // without a tree we have to do the select anyway
        if ($createMissingChildrenRows && ($this->childFound || ($this->fields === [] && $this->relations === []))) {
            $object = DataObject\Concrete::getById($oo_id);
            $classId = $object->getClassId();
            $query = sprintf(
                'WITH RECURSIVE cte(id, classId) AS (
                    SELECT c.id AS id, c.classId AS classId
                    FROM objects c
                    WHERE c.parentid = ?
                    UNION ALL
                    SELECT p.id AS id, p.classId AS classId
                    FROM objects p
                    INNER JOIN cte ON (p.parentid = cte.id)
                )
                SELECT x.id
                FROM cte x
                LEFT JOIN %s l ON (x.id = l.%s)
                WHERE x.classId = ?
                AND l.%s IS NULL',
                $this->querytable,
                $this->idField,
                $this->queryIdField
            );
            $missingIds = $this->db->fetchFirstColumn($query, [$object->getId(), $classId]);
            // create entries for children that don't have an entry yet
            $originalEntry = Helper::quoteDataIdentifiers($this->db, $this->db->fetchAssociative(
                sprintf('SELECT * FROM %s WHERE %s = ?', $this->querytable, $this->idField),
                [$oo_id]
            ));
            foreach ($missingIds as $id) {
                $originalEntry[$this->db->quoteIdentifier($this->idField)] = $id;
                $this->db->insert($this->db->quoteIdentifier($this->querytable), $originalEntry);
            }
        }
    }

    /**
     * Currently solely used for object bricks. If a brick is removed, this info must be propagated to all
     * child elements.
     */
    public function doDelete(int $objectId, array $params = []): void
    {
        // NOT FINISHED - NEEDS TO BE COMPLETED !!!

        // as a first step, build an ID list of all child elements that are affected. Stop at the level
        // which has a non-empty value.

        $fields = implode('`,`', $this->fields);
        if (!empty($fields)) {
            $fields = ', `' . $fields . '`';
        }

        $o = [
            'id' => $objectId,
            'children' => $this->buildTree($objectId, $fields, null, $params),
        ];

        foreach ($this->fields as $fieldname) {
            foreach ($o['children'] as $c) {
                $this->getIdsToCheckForDeletionForValuefields($c, $fieldname);
            }
            if (isset($this->deletionFieldIds[$fieldname])) {
                $this->updateQueryTableOnDelete($objectId, $this->deletionFieldIds[$fieldname], $fieldname);
            }
        }

        foreach (array_keys($this->relations) as $fieldname) {
            foreach ($o['children'] as $c) {
                $this->getIdsToCheckForDeletionForRelationfields($c, $fieldname);
            }
            if (isset($this->deletionFieldIds[$fieldname])) {
                $this->updateQueryTableOnDelete($objectId, $this->deletionFieldIds[$fieldname], $fieldname);
            }
        }

        $affectedIds = [];

        foreach ($this->deletionFieldIds as $fieldname => $ids) {
            foreach ($ids as $id) {
                $affectedIds[$id] = $id;
            }
        }

        $systemFields = ['id', 'fieldname'];

        $toBeRemovedItemIds = [];

        // now iterate over all affected elements and check if the object even has a brick. If it doesn't, then
        // remove the query row entirely ...
        if ($affectedIds) {
            $objectsWithBrickIds = [];
            $objectsWithBricks = $this->db->fetchAllAssociative(
                sprintf('SELECT %s FROM %s WHERE %s IN (?)', $this->idField, $this->storetable, $this->idField),
                [$affectedIds],
                [ArrayParameterType::INTEGER]
            );
            foreach ($objectsWithBricks as $item) {
                $objectsWithBrickIds[] = $item[$this->idField];
            }

            $currentQueryItems = $this->db->fetchAllAssociative(
                sprintf('SELECT * FROM %s WHERE %s IN (?)', $this->querytable, $this->idField),
                [$affectedIds],
                [ArrayParameterType::INTEGER]
            );

            foreach ($currentQueryItems as $queryItem) {
                $toBeRemoved = true;
                foreach ($queryItem as $fieldname => $value) {
                    if (in_array($fieldname, $systemFields)) {
                        continue;
                    }
                    if (is_null($value)) {
                        continue;
                    }
                    $toBeRemoved = false;

                    break;
                }
                if ($toBeRemoved && !in_array($queryItem['id'], $objectsWithBrickIds)) {
                    $toBeRemovedItemIds[] = $queryItem['id'];
                }
            }
        }

        if ($toBeRemovedItemIds) {
            $this->db->executeStatement(sprintf('DELETE FROM %s WHERE %s IN (?)', $this->querytable, $this->idField), [$toBeRemovedItemIds], [ArrayParameterType::INTEGER]);
        }
    }

    protected function filterResultByLanguage(array $result, string $language, string $column): array
    {
        $filteredResult = [];
        foreach ($result as $row) {
            $rowId = $row['id'];
            if ((!isset($filteredResult[$rowId]) && $row[$column] === null) || $row[$column] === $language) {
                $filteredResult[$rowId] = $row;
            }
        }

        return array_values($filteredResult);
    }

    protected function buildTree(int $currentParentId, string $fields = '', ?array $parentIdGroups = null, array $params = []): array
    {
        $objects = [];
        $storeTable = $this->storetable;
        $idfield = $this->idField;

        if (!$parentIdGroups) {
            if (isset($params['language'])) {
                $language = $params['language'];

                $query = sprintf(
                    'WITH RECURSIVE cte(id, classId, parentId, path) AS (
                        SELECT c.id AS id, c.classId AS classId, c.parentid AS parentId, c.path AS `path`
                        FROM objects c
                        WHERE c.parentid = ?
                        UNION ALL
                        SELECT p.id AS id, p.classId AS classId, p.parentid AS parentId, p.path AS `path`
                        FROM objects p
                        INNER JOIN cte ON (p.parentid = cte.id)
                    )
                    SELECT l.language AS `language`,
                           x.id AS id,
                           x.classId AS classId,
                           x.parentId AS parentId
                           %s
                    FROM cte x
                    LEFT JOIN %s l ON x.id = l.%s
                    WHERE COALESCE(`language`, ?) = ?
                    ORDER BY x.path ASC',
                    $fields,
                    $storeTable,
                    $idfield
                );

                $queryParams = [$currentParentId, $language, $language];
            } else {
                $language = null;

                $query = sprintf(
                    'WITH RECURSIVE cte(id, classId, parentId, path) AS (
                        SELECT c.id AS id, c.classId AS classId, c.parentid AS parentId, c.path AS `path`
                        FROM objects c
                        WHERE c.parentid = ?
                        UNION ALL
                        SELECT p.id AS id, p.classId AS classId, p.parentid AS parentId, p.path AS `path`
                        FROM objects p
                        INNER JOIN cte ON (p.parentid = cte.id)
                    )
                    SELECT x.id AS id,
                           x.classId AS classId,
                           x.parentId AS parentId
                           %s
                    FROM cte x
                    LEFT JOIN %s a ON x.id = a.%s
                    GROUP BY x.id
                    ORDER BY x.path ASC',
                    $fields,
                    $storeTable,
                    $idfield
                );

                $queryParams = [$currentParentId];
            }

            $queryCacheKey = 'tree_' . md5($query . '|' . $currentParentId . '|' . ($language ?? ''));

            if (self::$useRuntimeCache) {
                $parentIdGroups = self::$runtimeCache[$queryCacheKey] ?? null;
            }

            if (!$parentIdGroups) {
                $result = $this->db->fetchAllAssociative($query, $queryParams);

                if (isset($params['language'])) {
                    $result = $this->filterResultByLanguage($result, $params['language'], 'language');
                }

                // group the results together based on the parent id's
                $parentIdGroups = [];
                $rowCount = count($result);
                for ($rowIdx = 0; $rowIdx < $rowCount; ++$rowIdx) {
                    // assign the reference
                    $rowData = &$result[$rowIdx];

                    if (!isset($parentIdGroups[$rowData['parentId']])) {
                        $parentIdGroups[$rowData['parentId']] = [];
                    }

                    $parentIdGroups[$rowData['parentId']][] = &$rowData;
                }
                if (self::$useRuntimeCache) {
                    self::$runtimeCache[$queryCacheKey] = $parentIdGroups;
                }
            }
        }

        if (isset($parentIdGroups[$currentParentId])) {
            $childData = $parentIdGroups[$currentParentId];
            $childCount = count($childData);
            for ($childIdx = 0; $childIdx < $childCount; ++$childIdx) {
                $rowData = &$childData[$childIdx];

                if ($rowData['classId'] == $this->classId) {
                    $this->childFound = true;
                }

                $id = $rowData['id'];

                $o = [
                    'id' => $id,
                    'children' => $this->buildTree($id, $fields, $parentIdGroups, $params),
                    'values' => $rowData,
                ];

                $objects[] = $o;
            }
        }

        return $objects;
    }

    protected function getRelationCondition(array $params = []): string
    {
        $condition = '';
        $parts = [];

        if (isset($params['inheritanceRelationContext'])) {
            foreach ($params['inheritanceRelationContext'] as $key => $value) {
                $parts[] = $this->db->quoteIdentifier($key) . ' = ' . $this->db->quote($value);
            }
            $condition = implode(' AND ', $parts);
        }
        if (count($parts) > 0) {
            return $condition . ' AND ';
        }

        return $condition;
    }

    protected function getRelationsForNode(array &$node, array $params = []): array
    {
        // if the relations are already set, skip here
        if (isset($node['relations'])) {
            return $node;
        }

        $relationCondition = $this->getRelationCondition($params);

        if (isset($params['language'])) {
            $objectRelationsResult = $this->db->fetchAllAssociative(
                sprintf(
                    'SELECT src_id as id, fieldname, position, count(*) as COUNT FROM %s WHERE %s src_id = ? AND fieldname IN(?) GROUP BY position, fieldname HAVING `position` = ? OR ISNULL(`position`)',
                    $this->relationtable, $relationCondition
                ),
                [$node['id'], array_keys($this->relations), $params['language']],
                [ParameterType::INTEGER, ArrayParameterType::STRING, ParameterType::STRING]
            );
            $objectRelationsResult = $this->filterResultByLanguage($objectRelationsResult, $params['language'], 'position');
        } else {
            $objectRelationsResult = $this->db->fetchAllAssociative(
                sprintf(
                    'SELECT fieldname, count(*) as COUNT FROM %s WHERE %s src_id = ? AND fieldname IN(?) GROUP BY fieldname',
                    $this->relationtable, $relationCondition
                ),
                [$node['id'], array_keys($this->relations)],
                [ParameterType::INTEGER, ArrayParameterType::STRING]
            );
        }

        $objectRelations = [];
        if ($objectRelationsResult !== []) {
            foreach ($objectRelationsResult as $orr) {
                if ($orr['COUNT'] > 0) {
                    $objectRelations[$orr['fieldname']] = $orr['fieldname'];
                }
            }
            $node['relations'] = $objectRelations;
        } else {
            $node['relations'] = [];
        }

        return $node;
    }

    protected function getIdsToCheckForDeletionForValuefields(array $currentNode, string $fieldname, array $params = []): void
    {
        $value = $currentNode['values'][$fieldname] ?? null;

        if (!$this->fieldDefinitions[$fieldname]->isEmpty($value)) {
            return;
        }

        $this->deletionFieldIds[$fieldname][] = $currentNode['id'];

        if (!empty($currentNode['children'])) {
            foreach ($currentNode['children'] as $c) {
                $this->getIdsToCheckForDeletionForValuefields($c, $fieldname, $params);
            }
        }
    }

    protected function getIdsToUpdateForValuefields(array $currentNode, string $fieldname): void
    {
        $value = $currentNode['values'][$fieldname] ?? null;
        if ($this->fieldDefinitions[$fieldname]->isEmpty($value)) {
            $this->fieldIds[$fieldname][] = $currentNode['id'];
            if (!empty($currentNode['children'])) {
                foreach ($currentNode['children'] as $c) {
                    $this->getIdsToUpdateForValuefields($c, $fieldname);
                }
            }
        }
    }

    protected function getIdsToCheckForDeletionForRelationfields(array $currentNode, string $fieldname): void
    {
        $this->getRelationsForNode($currentNode);
        $value = $currentNode['relations'][$fieldname] ?? null;
        if (!$this->fieldDefinitions[$fieldname]->isEmpty($value)) {
            return;
        }
        $this->deletionFieldIds[$fieldname][] = $currentNode['id'];

        if (!empty($currentNode['children'])) {
            foreach ($currentNode['children'] as $c) {
                $this->getIdsToCheckForDeletionForRelationfields($c, $fieldname);
            }
        }
    }

    protected function getIdsToUpdateForRelationfields(array $currentNode, string $fieldname, array $params = []): void
    {
        $this->getRelationsForNode($currentNode, $params);
        $value = $currentNode['relations'][$fieldname] ?? null;
        if ($this->fieldDefinitions[$fieldname]->isEmpty($value)) {
            $this->fieldIds[$fieldname][] = $currentNode['id'];
            if (!empty($currentNode['children'])) {
                foreach ($currentNode['children'] as $c) {
                    $this->getIdsToUpdateForRelationfields($c, $fieldname, $params);
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    protected function updateQueryTable(int $oo_id, array $ids, string $fieldname): void
    {
        if ($ids !== []) {
            $value = $this->db->fetchOne(
                sprintf('SELECT %s FROM %s WHERE %s = ?', $this->db->quoteIdentifier($fieldname), $this->querytable, $this->idField),
                [$oo_id]
            );
            $this->db->executeStatement(
                sprintf('UPDATE %s SET %s = ? WHERE %s IN (?)', $this->querytable, $this->db->quoteIdentifier($fieldname), $this->db->quoteIdentifier($this->idField)),
                [$value, $ids],
                [ParameterType::STRING, ArrayParameterType::INTEGER]
            );
        }
    }

    protected function updateQueryTableOnDelete(int $oo_id, array $ids, string $fieldname): void
    {
        if ($ids !== []) {
            $this->db->executeStatement(
                sprintf('UPDATE %s SET %s = ? WHERE %s IN (?)', $this->querytable, $this->db->quoteIdentifier($fieldname), $this->db->quoteIdentifier($this->idField)),
                [null, $ids],
                [ParameterType::NULL, ArrayParameterType::INTEGER]
            );
        }
    }
}
