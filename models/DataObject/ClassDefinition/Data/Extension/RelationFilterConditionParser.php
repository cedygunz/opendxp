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

namespace OpenDxp\Model\DataObject\ClassDefinition\Data\Extension;

use OpenDxp\Db\Helper;

/**
 * Trait RelationFilterConditionParser
 *
 * @package OpenDxp\Model\DataObject\ClassDefinition\Data\Extension
 */
trait RelationFilterConditionParser
{
    /**
     * Builds the filter condition for a relation field; pass $classId to use the fast indexed lookup instead of the old LIKE scan.
     */
    public function getRelationFilterCondition(?string $value, string $operator, string $name, string $brickPrefix = '', ?string $classId = null, ?string $destinationType = null): string
    {
        $db = \OpenDxp\Db::get();
        $key = $brickPrefix . $db->quoteIdentifier($name);
        $result = $key . ' IS NULL';
        if ($value === null || $value === 'null') {
            return $result;
        }

        $values = array_filter(explode(',', $value));

        if ($values === []) {
            return $result;
        }

        if ($classId !== null) {
            // Look up the relation table directly instead of scanning the text column with LIKE.
            $relationsTable = $db->quoteIdentifier('object_relations_' . $classId);
            $quotedClassId = $db->quote($classId);
            $quotedFieldName = $db->quote($name);
            $quotedOwnerType = $db->quote('object');

            $fieldConditions = array_map(
                static function (string $value) use ($relationsTable, $quotedClassId, $quotedFieldName, $quotedOwnerType, $destinationType, $db): string {
                    // A value can be a plain id ("60") or "type|id" ("object|60"); the embedded type wins if present.
                    $id = $value;
                    $type = $destinationType;

                    if (str_contains($value, '|')) {
                        [$type, $id] = explode('|', $value, 2);
                    }

                    $quotedId = $db->quote($id);
                    $typeCondition = $type !== null ? ' AND type = ' . $db->quote($type) : '';

                    $directMatch = sprintf(
                        'SELECT src_id FROM %s WHERE ownertype = %s AND fieldname = %s AND dest_id = %s%s',
                        $relationsTable,
                        $quotedOwnerType,
                        $quotedFieldName,
                        $quotedId,
                        $typeCondition
                    );

                    // Variants inherit the relation from their parent instead of storing their own row, so match those too.
                    $inheritedMatch = sprintf(
                        'SELECT child.id FROM objects child
                        WHERE child.classId = %s
                          AND NOT EXISTS (SELECT 1 FROM %s r WHERE r.src_id = child.id AND r.fieldname = %s)
                          AND child.parentId IN (%s)',
                        $quotedClassId,
                        $relationsTable,
                        $quotedFieldName,
                        $directMatch
                    );

                    return sprintf('id IN (%s UNION %s)', $directMatch, $inheritedMatch);
                },
                $values,
            );

            return '(' . implode(' AND ', $fieldConditions) . ')';
        }

        if ($operator === '=') {
            return $key . ' = ' . $db->quote($value);
        }

        $fieldConditions = array_map(static function ($value) use ($key, $db) {
            $escaped = Helper::escapeLike($value);
            // Match either a plain id or a "type|id" entry, since relations store either format.
            $quotedBare = $db->quote('%,' . $escaped . ',%');
            $quotedPrefixed = $db->quote('%|' . $escaped . ',%');

            return sprintf('(%1$s LIKE %2$s OR %1$s LIKE %3$s)', $key, $quotedBare, $quotedPrefixed);
        }, $values);

        return '(' . implode(' AND ', $fieldConditions) . ')';
    }
}
